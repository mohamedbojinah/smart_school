<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\School;
use App\Models\StudentPortfolio;
use App\Models\StudentPortfolioItem;

class StudentPortfolioController extends Controller
{
    // ==========================================
    // واجهة إدارة البورتفوليو للطالب
    // ==========================================
    public function studentIndex()
    {
        $student = auth()->user();
        $schoolId = $student->school_id ?? 1;

        $portfolio = StudentPortfolio::firstOrCreate(
            ['user_id' => $student->id],
            [
                'school_id' => $schoolId,
                'slug'      => Str::slug($student->name, '-', 'ar') . '-' . Str::lower(Str::random(5)),
                'headline'  => 'طالب متميز في المرحلة المدرسية وشغوف بالابتكار والتعلم المستمر',
                'about_me'  => 'أطمح لبناء مسيرة أكاديمية واعدة واكتساب مهارات القرن الحادي والعشرين عبر المشاريع والأنشطة المدرسية.',
                'skills'    => 'حل المشكلات, العمل الجماعي, مهارات البحث, التفكير النقدي',
                'volunteer_hours' => 15,
                'is_public' => true,
                'verified_by_school' => true,
                'verification_seal_number' => 'PORT-' . date('Y') . '-' . strtoupper(Str::random(6)),
            ]
        );

        $portfolio->load('items');

        return view('student.portfolio.index', compact('portfolio', 'student'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'headline'        => 'required|string|max:255',
            'about_me'        => 'nullable|string|max:2000',
            'skills'          => 'nullable|string|max:500',
            'volunteer_hours' => 'nullable|integer|min:0|max:1000',
            'is_public'       => 'nullable|boolean',
        ]);

        $student = auth()->user();
        $portfolio = StudentPortfolio::where('user_id', $student->id)->firstOrFail();

        $portfolio->update([
            'headline'        => $request->headline,
            'about_me'        => $request->about_me,
            'skills'          => $request->skills,
            'volunteer_hours' => (int)($request->volunteer_hours ?? 0),
            'is_public'       => $request->has('is_public'),
        ]);

        return redirect()->back()->with('success', 'تم حفظ وتحديث بيانات سجلك الرقمي بنجاح 🌟');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|in:project,certificate,competition,research,art_or_sport',
            'description' => 'nullable|string|max:1500',
            'project_url' => 'nullable|url|max:255',
            'year'        => 'nullable|string|max:10',
        ]);

        $student = auth()->user();
        $portfolio = StudentPortfolio::where('user_id', $student->id)->firstOrFail();

        StudentPortfolioItem::create([
            'student_portfolio_id' => $portfolio->id,
            'title'                => $request->title,
            'category'             => $request->category,
            'description'          => $request->description,
            'project_url'          => $request->project_url,
            'year'                 => $request->year ?? date('Y'),
        ]);

        return redirect()->back()->with('success', 'تمت إضافة الإنجاز بنجاح إلى سجلك الموثق 🏆');
    }

    public function destroyItem($id)
    {
        $student = auth()->user();
        $portfolio = StudentPortfolio::where('user_id', $student->id)->firstOrFail();

        $item = StudentPortfolioItem::where('student_portfolio_id', $portfolio->id)->findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'تم حذف الإنجاز بنجاح.');
    }

    // ==========================================
    // الصفحة العامة الموثقة للبورتفوليو (Public Showcase)
    // ==========================================
    public function publicShow($slug)
    {
        $portfolio = StudentPortfolio::where('slug', $slug)
            ->with(['student.studentProfile.schoolClass.grade', 'items', 'school'])
            ->firstOrFail();

        $student = $portfolio->student;
        $school = $portfolio->school ?? School::find(1);

        return view('portfolio.show', compact('portfolio', 'student', 'school'));
    }

    // ==========================================
    // استعراض ولي الأمر لبورتفوليو ابنه
    // ==========================================
    public function parentView($childId)
    {
        $parent = auth()->user();
        
        // التحقق من أن الطالب ابن لولي الأمر
        $isChild = \Illuminate\Support\Facades\DB::table('parent_student')
            ->where('parent_id', $parent->id)
            ->where('student_id', $childId)
            ->exists();

        if (!$isChild && $parent->role !== 'admin' && $parent->role !== 'manager') {
            abort(403, 'غير مصرح لك باستعراض سجل هذا الطالب.');
        }

        $student = User::findOrFail($childId);
        $portfolio = StudentPortfolio::firstOrCreate(
            ['user_id' => $student->id],
            [
                'school_id' => $student->school_id ?? 1,
                'slug'      => Str::slug($student->name, '-', 'ar') . '-' . Str::lower(Str::random(5)),
                'headline'  => 'طالب متميز في المرحلة المدرسية وشغوف بالابتكار والتعلم المستمر',
                'about_me'  => 'سجل إنجازات رقمي موثق ومصادق عليه من إدارة المدرسة.',
                'skills'    => 'حل المشكلات, البحث العلمي, الإلقاء والخطابة',
                'volunteer_hours' => 10,
                'is_public' => true,
                'verified_by_school' => true,
                'verification_seal_number' => 'PORT-' . date('Y') . '-' . strtoupper(Str::random(6)),
            ]
        );

        return redirect()->route('portfolio.public', $portfolio->slug);
    }
}
