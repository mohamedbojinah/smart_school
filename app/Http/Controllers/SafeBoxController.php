<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SafeBoxReport;
use App\Models\StudentWellbeingPulse;
use App\Models\User;

class SafeBoxController extends Controller
{
    // ==========================================
    // واجهات الطالب (Student Portal)
    // ==========================================

    public function studentIndex(Request $request)
    {
        $student = auth()->user();
        $schoolId = $student->school_id ?? 1;

        // التحقق من تسجيل نبض المشاعر لهذا الأسبوع
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $thisWeekPulse = StudentWellbeingPulse::where('student_id', $student->id)
            ->where('week_date', $startOfWeek)
            ->first();

        // البلاغات غير المجهولة التي أرسلها هذا الطالب (إن اختار الإفصاح عن اسمه)
        $myReports = SafeBoxReport::where('student_id', $student->id)
            ->latest()
            ->get();

        // فحص نتيجة البحث برمز التتبع إن تم إرساله
        $trackedReport = null;
        if ($request->has('token') && !empty($request->token)) {
            $trackedReport = SafeBoxReport::where('token', trim($request->token))->first();
        }

        return view('student.safebox.index', compact('thisWeekPulse', 'myReports', 'trackedReport'));
    }

    public function submitReport(Request $request)
    {
        $request->validate([
            'category'    => 'required|in:bullying,mental_stress,family_issue,academic_pressure,safety_hazard,other',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'urgency'     => 'required|in:normal,high,critical',
            'is_anonymous'=> 'nullable|boolean',
        ]);

        $student = auth()->user();
        $token = 'SAFE-' . strtoupper(Str::random(6));

        $report = SafeBoxReport::create([
            'school_id'    => $student->school_id ?? 1,
            'student_id'   => $request->boolean('is_anonymous') ? null : $student->id,
            'token'        => $token,
            'category'     => $request->category,
            'title'        => $request->title,
            'description'  => $request->description,
            'urgency'      => $request->urgency,
            'status'       => 'pending',
        ]);

        return redirect()->route('student.safebox.index', ['token' => $token])
            ->with('success', "تم إرسال بلاغك السري بأمان تام 🛡️. يرجى الاحتفاظ برمز المتابعة السري: {$token}");
    }

    public function submitMoodPulse(Request $request)
    {
        $request->validate([
            'mood' => 'required|in:great,calm,anxious,sad,stressed',
            'note' => 'nullable|string|max:500',
        ]);

        $student = auth()->user();
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');

        $emojis = [
            'great'    => '😃',
            'calm'     => '😌',
            'anxious'  => '😟',
            'sad'      => '😢',
            'stressed' => '😫',
        ];

        StudentWellbeingPulse::updateOrCreate(
            [
                'student_id' => $student->id,
                'week_date'  => $startOfWeek,
            ],
            [
                'school_id' => $student->school_id ?? 1,
                'mood'      => $request->mood,
                'emoji'     => $emojis[$request->mood] ?? '😌',
                'note'      => $request->note,
            ]
        );

        return redirect()->back()->with('success', 'شكراً لك! تم تسجيل نبض مشاعرك الأسبوعي بنجاح 💖');
    }

    // ==========================================
    // واجهات الإدارة والمرشد والمدير (Admin & Manager)
    // ==========================================

    public function adminIndex(Request $request)
    {
        $schoolId = auth()->user()->school_id ?? 1;

        $query = SafeBoxReport::where('school_id', $schoolId)->with(['student', 'counselor'])->latest();

        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('urgency') && $request->urgency != '') {
            $query->where('urgency', $request->urgency);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(15)->withQueryString();

        // إحصائيات سريعة للبلاغات
        $stats = [
            'total'     => SafeBoxReport::where('school_id', $schoolId)->count(),
            'pending'   => SafeBoxReport::where('school_id', $schoolId)->where('status', 'pending')->count(),
            'critical'  => SafeBoxReport::where('school_id', $schoolId)->where('urgency', 'critical')->count(),
            'bullying'  => SafeBoxReport::where('school_id', $schoolId)->where('category', 'bullying')->count(),
        ];

        // إحصائيات نبض المشاعر والرفاهية للأسبوع الحالي
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $moodCounts = StudentWellbeingPulse::where('school_id', $schoolId)
            ->where('week_date', $startOfWeek)
            ->selectRaw('mood, count(*) as count')
            ->groupBy('mood')
            ->pluck('count', 'mood')
            ->toArray();

        $moodLabels = ['ممتاز 😃', 'هادئ 😌', 'قلق 😟', 'حزين 😢', 'مضغوط 😫'];
        $moodKeys   = ['great', 'calm', 'anxious', 'sad', 'stressed'];
        $moodData   = array_map(fn($k) => $moodCounts[$k] ?? 0, $moodKeys);

        return view('admin.safebox.index', compact('reports', 'stats', 'moodLabels', 'moodData'));
    }

    public function adminRespond(Request $request, $id)
    {
        $request->validate([
            'counselor_response' => 'required|string',
            'status'             => 'required|in:in_review,resolved',
        ]);

        $schoolId = auth()->user()->school_id ?? 1;
        $report = SafeBoxReport::where('school_id', $schoolId)->findOrFail($id);

        $report->update([
            'counselor_response' => $request->counselor_response,
            'status'             => $request->status,
            'counselor_id'       => auth()->id(),
            'responded_at'       => now(),
        ]);

        return redirect()->back()->with('success', 'تم إرسال التوجيه والرد السري على البلاغ بنجاح ✅');
    }
}
