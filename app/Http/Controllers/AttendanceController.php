<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index($sectionId)
{
    $section = DB::table('classes')->find($sectionId);
    $grade = DB::table('grades')->find($section->grade_id);
    $students = \App\Models\User::role('student')
        ->whereHas('studentProfile', function($q) use ($sectionId) {
            $q->where('class_id', $sectionId);
        })->orderBy('name')->get();

    // جلب غياب اليوم إذا كان قد رُصد مسبقاً
    $todayAttendance = DB::table('attendances')
        ->where('section_id', $sectionId)
        ->where('attendance_date', date('Y-m-d'))
        ->pluck('status', 'student_id');

    return view('teacher.attendance.index', compact('section', 'grade', 'students', 'todayAttendance'));
}

    public function store(Request $request, $id = null)
    {
        $studentId = $id ?? $request->route('id') ?? $request->input('id') ?? $request->input('student_id');
        $status = $request->input('status');

        if ($studentId && $status) {
            \App\Models\Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'attendance_date' => date('Y-m-d')
                ],
                [
                    'user_id' => auth()->id() ?? 1,
                    'status' => $status,
                ]
            );
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success']);
            }
            return back()->with('success', 'تم تسجيل الحضور بنجاح ✅');
        }

        if ($request->has('attendance') && is_iterable($request->attendance)) {
            $sectionId = $request->section_id;
            $date = date('Y-m-d');

            foreach ($request->attendance as $sId => $st) {
                \App\Models\Attendance::updateOrCreate(
                    [
                        'student_id' => $sId,
                        'attendance_date' => $date
                    ],
                    [
                        'user_id' => auth()->id() ?? 1,
                        'class_id' => $sectionId,
                        'status' => $st,
                    ]
                );
            }

            return back()->with('success', 'تم حفظ غياب اليوم بنجاح ✅');
        }

        return back();
    }

    /**
     * Show live QR camera scanner interface for supervisor/teacher.
     */
    public function scanner()
    {
        $today = date('Y-m-d');
        $todayCount = \App\Models\Attendance::where('attendance_date', $today)
            ->where('status', 'present')
            ->count();

        return view('attendance.scanner', compact('todayCount'));
    }

    /**
     * Process scanned QR code from camera or manual entry.
     */
    public function scanQr(\Illuminate\Http\Request $request)
    {
        $raw = $request->input('code') ?? $request->input('qr_code') ?? $request->input('student_id');
        if (!$raw) {
            return response()->json(['success' => false, 'message' => 'لم يتم إرسال بيانات الباركود.'], 422);
        }

        $studentId = null;
        if (is_numeric($raw)) {
            $studentId = (int)$raw;
        } elseif (preg_match('/(?:STD-|ID-|student\/)(\d+)/i', $raw, $matches)) {
            $studentId = (int)$matches[1];
        } else {
            $studentId = (int)$raw;
        }

        $user = \App\Models\User::with(['studentProfile.schoolClass.grade', 'school'])->find($studentId);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على طالب مسجل بهذا الرمز.'], 404);
        }

        if (auth()->check() && auth()->user()->school_id && $user->school_id != auth()->user()->school_id) {
            return response()->json(['success' => false, 'message' => 'الطالب ينتمي لمدرسة أخرى.'], 403);
        }

        $profile = $user->studentProfile;
        $classId = $profile ? ($profile->class_id ?? $profile->section_id) : null;
        $className = ($profile && $profile->schoolClass) ? ($profile->schoolClass->name ?? '') : 'غير محدد';
        $studentProfileId = $profile ? $profile->id : $user->id;

        $today = date('Y-m-d');
        $existing = \App\Models\Attendance::where('student_id', $studentProfileId)
            ->where('attendance_date', $today)
            ->first();

        $alreadyPresent = ($existing && $existing->status === 'present');

        \App\Models\Attendance::updateOrCreate(
            [
                'student_id' => $studentProfileId,
                'attendance_date' => $today
            ],
            [
                'user_id' => auth()->id() ?? 1,
                'class_id' => $classId,
                'status' => 'present',
            ]
        );

        // إرسال إشعار فوري لولي الأمر بهاتف المحمول
        if (!$alreadyPresent) {
            $parentIds = DB::table('parent_student')->where('student_id', $user->id)->pluck('parent_id');
            foreach ($parentIds as $parentId) {
                \App\Services\PushNotificationService::sendToUser(
                    $parentId,
                    '📍 إشعار حضور في المدرسة',
                    "تم تسجيل حضور ابنكم {$user->name} في المدرسة في تمام الساعة " . now()->format('h:i A'),
                    ['student_id' => (string)$user->id, 'type' => 'attendance'],
                    'attendance',
                    $user->school_id,
                    $user->id
                );
            }
        }

        return response()->json([
            'success' => true,
            'already_registered' => $alreadyPresent,
            'message' => $alreadyPresent ? 'الطالب مسجل حضور مسبقاً لهذا اليوم' : 'تم تسجيل حضور الطالب بنجاح ✅',
            'student' => [
                'id' => $user->id,
                'name' => $user->name,
                'class' => $className,
                'photo' => $user->photo ? asset($user->photo) : null,
                'time' => now()->format('H:i:s'),
                'date' => $today
            ]
        ]);
    }
}
