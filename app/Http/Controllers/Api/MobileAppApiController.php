<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\AssessmentMark;
use App\Models\Attendance;
use App\Models\FeePayment;
use App\Models\Homework;
use App\Models\LeaveRequest;
use App\Models\SchoolAnnouncement;
use App\Models\DeviceToken;
use App\Models\AppNotification;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Assessment;
use App\Models\SafeBoxReport;
use App\Models\StudentWellbeingPulse;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamAttempt;
use App\Models\StudentPortfolio;
use App\Models\StudentPortfolioItem;
use App\Models\OfficialLetter;
use App\Models\Message;
use App\Models\Expense;
use App\Models\ManagerApproval;
use App\Services\AiEducationService;
use Illuminate\Support\Str;
use App\Services\PushNotificationService;

class MobileAppApiController extends Controller
{
    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('school')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات الدخول غير صحيحة، يرجى التأكد من البريد وكلمة المرور'
            ], 401);
        }

        // إنشاء توكن وصول Sanctum
        $token = $user->createToken('smart_school_mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'school_id' => $user->school_id,
                'school_name' => $user->school->name ?? 'المدرسة الذكية',
            ]
        ]);
    }

    /**
     * جلب قائمة كافة المدارس المسجلة في النظام
     */
    public function getSchools()
    {
        $schools = School::select('id', 'name', 'academic_year')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $schools
        ]);
    }

    /**
     * لوحة تحكم ولي الأمر الشاملة متعددة المدارس (Multi-School Parent Dashboard)
     */
    public function getParentDashboard(Request $request)
    {
        $parent = $request->user();

        // جلب جميع الأبناء المرتبطين بولي الأمر
        $studentUserIds = DB::table('parent_student')
            ->where('parent_id', $parent->id)
            ->pluck('student_id');

        $childrenProfiles = StudentProfile::whereIn('user_id', $studentUserIds)
            ->with(['user.school', 'schoolClass.grade'])
            ->get();

        $schoolsMap = [];
        $childrenData = [];

        foreach ($childrenProfiles as $profile) {
            $studentUser = $profile->user;
            if (!$studentUser) continue;

            $school = $studentUser->school;
            $schoolId = $school ? $school->id : 1;
            $schoolName = $school ? $school->name : 'المدرسة الذكية';

            // تجميع المدارس التي يدرس بها أبناء ولي الأمر
            if (!isset($schoolsMap[$schoolId])) {
                $schoolsMap[$schoolId] = [
                    'id' => $schoolId,
                    'name' => $schoolName,
                    'children_count' => 0,
                ];
            }
            $schoolsMap[$schoolId]['children_count']++;

            // إحصائيات الدرجات
            $marks = AssessmentMark::where('student_id', $studentUser->id)
                ->whereNotNull('score')
                ->with('assessment.subject')
                ->get();

            $totalScore = 0;
            $totalMax = 0;
            $gradesList = [];

            foreach ($marks as $mark) {
                if ($mark->assessment && $mark->assessment->subject) {
                    $s = (float)$mark->score;
                    $m = (float)($mark->assessment->max_score > 0 ? $mark->assessment->max_score : 100);
                    $totalScore += $s;
                    $totalMax += $m;

                    $gradesList[] = [
                        'subject_name' => $mark->assessment->subject->name,
                        'title' => $mark->assessment->name ?? 'تقييم',
                        'score' => $s,
                        'max_score' => $m,
                        'percentage' => $m > 0 ? round(($s / $m) * 100) : 0,
                        'term' => $mark->assessment->semester ?? 1,
                    ];
                }
            }

            $overallPercentage = $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 1) : 0;

            // إحصائيات الحضور
            $presentCount = Attendance::where('student_id', $studentUser->id)->where('status', 'present')->count();
            $absentCount = Attendance::where('student_id', $studentUser->id)->where('status', 'absent')->count();
            $lateCount = Attendance::where('student_id', $studentUser->id)->where('status', 'late')->count();

            // الرسوم المالية
            $totalFees = (float)$profile->total_fees;
            $paidFees = (float)$profile->paid_fees;
            $remainingFees = max(0, $totalFees - $paidFees);

            $childrenData[] = [
                'id' => $profile->id,
                'user_id' => $studentUser->id,
                'name' => $studentUser->name,
                'email' => $studentUser->email,
                'student_code' => 'STU-' . str_pad($studentUser->id, 4, '0', STR_PAD_LEFT),
                'school_id' => $schoolId,
                'school_name' => $schoolName,
                'class_name' => $profile->schoolClass->name ?? 'غير محدد',
                'grade_name' => $profile->schoolClass->grade->name ?? '',
                'wallet_balance' => (float)$profile->wallet_balance,
                'daily_limit' => (float)$profile->daily_limit,
                'wallet_pin' => $profile->wallet_pin,
                'fees' => [
                    'total' => $totalFees,
                    'paid' => $paidFees,
                    'remaining' => $remainingFees,
                ],
                'attendance' => [
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'total' => $presentCount + $absentCount + $lateCount,
                ],
                'academics' => [
                    'percentage' => $overallPercentage,
                    'total_score' => $totalScore,
                    'max_score' => $totalMax,
                    'grades' => $gradesList,
                ]
            ];
        }

        // إعلانات المدرسة
        $announcements = SchoolAnnouncement::where('is_active', true)
            ->whereIn('target_role', ['all', 'parent'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'email' => $parent->email,
                ],
                'schools' => array_values($schoolsMap),
                'children' => $childrenData,
                'announcements' => $announcements,
            ]
        ]);
    }

    /**
     * 🛡️ فحص أمني: التحقق من أن المستخدم الحالي هو ولي أمر الطالب أو مدير النظام
     */
    protected function checkParentStudentAccess($parentUser, $studentUserId): bool
    {
        if (!$parentUser || !$studentUserId) {
            return false;
        }

        // المدراء والمسؤولون يملكون صلاحية إدارية عليا
        if (in_array($parentUser->role, ['admin', 'manager', 'super_admin'])) {
            return true;
        }

        // فحص جدول الربط بين ولي الأمر والطلاب
        return DB::table('parent_student')
            ->where('parent_id', $parentUser->id)
            ->where('student_id', $studentUserId)
            ->exists();
    }

    /**
     * تحديث إعدادات محفظة الابن (PIN والحد اليومي)
     */
    public function updateWalletSettings(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
        ]);

        $profile = StudentProfile::find($request->student_id)
            ?? StudentProfile::where('user_id', $request->student_id)->first();

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'ملف الطالب غير موجود',
            ], 404);
        }

        // 🛡️ فحص أمني: منع التعديل على محفظة طالب لا ينتمي لولي الأمر الحالي
        if (!$this->checkParentStudentAccess($request->user(), $profile->user_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بتعديل بيانات هذا الطالب',
            ], 403);
        }

        if ($request->has('wallet_pin')) {
            $profile->wallet_pin = $request->wallet_pin;
        }

        if ($request->has('daily_limit')) {
            $profile->daily_limit = $request->daily_limit;
        }

        $profile->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حفظ إعدادات المحفظة بنجاح',
            'data' => [
                'wallet_balance' => (float)$profile->wallet_balance,
                'daily_limit' => (float)$profile->daily_limit,
                'wallet_pin' => $profile->wallet_pin,
            ]
        ]);
    }

    /**
     * تقديم طلب استئذان / غياب
     */
    public function submitLeaveRequest(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'type' => 'required|in:absence,early_leave',
            'leave_date' => 'required|date',
            'reason' => 'required|string',
        ]);

        $profile = StudentProfile::find($request->student_id)
            ?? StudentProfile::where('user_id', $request->student_id)->first();

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'ملف الطالب غير موجود',
            ], 404);
        }

        // 🛡️ فحص أمني: منع تقديم طلب استئذان لطالب لا ينتمي لولي الأمر الحالي
        if (!$this->checkParentStudentAccess($request->user(), $profile->user_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بتقديم طلب استئذان لهذا الطالب',
            ], 403);
        }

        $studentProfileId = $profile->id;

        $leave = LeaveRequest::create([
            'parent_id' => $request->user()->id,
            'student_id' => $studentProfileId,
            'type' => $request->type,
            'leave_date' => $request->leave_date,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تقديم طلب الاستئذان بنجاح',
            'data' => $leave
        ]);
    }

    /**
     * جلب طلبات الاستئذان السابقة لولي الأمر
     */
    public function getLeaveRequests(Request $request)
    {
        $leaves = LeaveRequest::where('parent_id', $request->user()->id)
            ->with(['student.user'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $leaves
        ]);
    }

    /**
     * جلب قائمة الواجبات المدرسية للأبناء
     */
    public function getHomeworks(Request $request)
    {
        $parent = $request->user();
        $studentUserIds = DB::table('parent_student')
            ->where('parent_id', $parent->id)
            ->pluck('student_id');

        $classIds = StudentProfile::whereIn('user_id', $studentUserIds)
            ->pluck('class_id')
            ->filter();

        $homeworks = Homework::whereIn('class_id', $classIds)
            ->with(['subject', 'teacher'])
            ->latest('due_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $homeworks
        ]);
    }

    /**
     * تسجيل رمز جهاز المستخدم لإشعارات FCM
     */
    public function registerDeviceToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
        ]);

        $token = DeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'fcm_token' => $request->fcm_token,
            ],
            [
                'device_type' => $request->device_type ?? 'android',
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الجهاز بنجاح لاستقبال الإشعارات',
            'data' => $token
        ]);
    }

    /**
     * جلب قائمة الإشعارات الخاصة بالمستخدم
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $unreadCount = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]
        ]);
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $notification = AppNotification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديد الإشعار كمقروء'
        ]);
    }

    /**
     * تحديد كافة الإشعارات كمقروءة
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديد جميع الإشعارات كمقروءة'
        ]);
    }

    /**
     * إرسال إشعار تجريبي فوري للجهاز
     */
    public function sendTestNotification(Request $request)
    {
        $user = $request->user();

        $notification = PushNotificationService::sendToUser(
            $user,
            '🔔 إشعار تجريبي من Smart School',
            'هذا إشعار تجريبي لاختبار وصول التنبيهات حتى عند إغلاق التطبيق بنجاح!',
            [
                'screen' => 'dashboard',
                'timestamp' => now()->toIso8601String(),
            ],
            'general'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال الإشعار التجريبي بنجاح!',
            'data' => $notification
        ]);
    }

    // =========================================================================
    // 🎓 1. واجهات الطالب (Student Mobile API)
    // =========================================================================

    /**
     * لوحة تحكم الطالب (Student Dashboard)
     */
    public function getStudentDashboard(Request $request)
    {
        $user = $request->user();
        $studentProfile = StudentProfile::where('user_id', $user->id)
            ->with(['schoolClass.grade', 'user.school'])
            ->first();

        if (!$studentProfile) {
            return response()->json([
                'status' => 'error',
                'message' => 'ملف الطالب غير موجود',
            ], 404);
        }

        $class = $studentProfile->schoolClass;
        $classId = $class ? $class->id : null;
        $grade = $class ? $class->grade : null;
        $gradeId = $grade ? $grade->id : null;

        // 1. المواد الدراسية الخاصة بالمرحلة مع المعلمين
        $subjects = [];
        if ($gradeId) {
            $rawSubjects = Subject::where('grade_id', $gradeId)->get();
            foreach ($rawSubjects as $subj) {
                $teacherName = null;
                if ($classId) {
                    $assignment = DB::table('teacher_subject_section')
                        ->join('users', 'teacher_subject_section.teacher_id', '=', 'users.id')
                        ->where('teacher_subject_section.subject_id', $subj->id)
                        ->where('teacher_subject_section.section_id', $classId)
                        ->select('users.name')
                        ->first();
                    $teacherName = $assignment->name ?? 'مدرس المادة';
                }
                $subjects[] = [
                    'id' => $subj->id,
                    'name' => $subj->name,
                    'teacher_name' => $teacherName,
                    'total_score' => $subj->total_score ?? 100,
                ];
            }
        }

        // 2. جدول الحصص اليومي
        $daysMap = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
        $todayEnglish = $daysMap[now()->dayOfWeek] ?? 'Sunday';
        $todaySchedule = [];
        if ($classId) {
            $todaySchedule = Schedule::where('class_id', $classId)
                ->where('day', $todayEnglish)
                ->with(['subject', 'teacher'])
                ->orderBy('period')
                ->get()
                ->map(function ($s) {
                    return [
                        'period' => $s->period,
                        'subject_name' => $s->subject->name ?? 'حصة',
                        'teacher_name' => $s->teacher->name ?? 'معلم',
                    ];
                });
        }

        // 3. آخر الواجبات المدرسية
        $homeworks = [];
        if ($classId) {
            $homeworks = Homework::where('class_id', $classId)
                ->with(['subject', 'teacher'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'title' => $h->title,
                        'description' => $h->description,
                        'subject_name' => $h->subject->name ?? 'المادة',
                        'teacher_name' => $h->teacher->name ?? 'المعلم',
                        'due_date' => $h->due_date,
                    ];
                });
        }

        // 4. إحصائيات الحضور والغياب
        $attendances = Attendance::where('student_id', $studentProfile->id)
            ->orWhere('user_id', $user->id)
            ->get();
        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $lateDays = $attendances->where('status', 'late')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'student' => [
                    'id' => $user->id,
                    'profile_id' => $studentProfile->id,
                    'name' => $user->name,
                    'student_number' => $studentProfile->student_number ?? 'STU-' . str_pad($studentProfile->id, 4, '0', STR_PAD_LEFT),
                    'school_name' => $user->school->name ?? 'المدرسة الذكية',
                    'grade_name' => $grade->name ?? 'المرحلة الدراسية',
                    'class_name' => $class ? ($class->name ?? 'فصل ' . $class->section) : 'غير مسجل بفصل',
                    'wallet_balance' => (float)($studentProfile->wallet_balance ?? 0),
                    'photo' => $user->photo,
                ],
                'attendance_summary' => [
                    'total' => $attendances->count(),
                    'present' => $presentDays,
                    'absent' => $absentDays,
                    'late' => $lateDays,
                    'rate' => $attendances->count() > 0 ? round(($presentDays / $attendances->count()) * 100, 1) : 100,
                ],
                'subjects' => $subjects,
                'today_schedule' => $todaySchedule,
                'recent_homeworks' => $homeworks,
            ]
        ]);
    }

    /**
     * كشف درجات الطالب وعلاماته التفصيلية
     */
    public function getStudentGrades(Request $request)
    {
        $user = $request->user();
        $studentProfile = StudentProfile::where('user_id', $user->id)->first();

        if (!$studentProfile) {
            return response()->json(['status' => 'error', 'message' => 'ملف الطالب غير موجود'], 404);
        }

        $marks = AssessmentMark::where('student_id', $studentProfile->id)
            ->with(['assessment.subject'])
            ->get();

        $groupedGrades = [];
        foreach ($marks as $m) {
            $subjectName = $m->assessment->subject->name ?? 'مادة عامة';
            if (!isset($groupedGrades[$subjectName])) {
                $groupedGrades[$subjectName] = [
                    'subject' => $subjectName,
                    'total_marks' => 0,
                    'max_marks' => 0,
                    'items' => [],
                ];
            }
            $score = (float)$m->score;
            $maxScore = (float)($m->assessment->max_score ?? 100);
            $groupedGrades[$subjectName]['total_marks'] += $score;
            $groupedGrades[$subjectName]['max_marks'] += $maxScore;
            $groupedGrades[$subjectName]['items'][] = [
                'title' => $m->assessment->title ?? 'تقييم',
                'type' => $m->assessment->type ?? 'activity',
                'score' => $score,
                'max_score' => $maxScore,
                'date' => $m->assessment->date ?? null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => array_values($groupedGrades),
        ]);
    }

    /**
     * جدول الحصص الأسبوعي للطالب
     */
    public function getStudentSchedule(Request $request)
    {
        $user = $request->user();
        $studentProfile = StudentProfile::where('user_id', $user->id)->first();
        $classId = $studentProfile->class_id ?? null;

        if (!$classId) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $schedules = Schedule::where('class_id', $classId)
            ->with(['subject', 'teacher'])
            ->orderBy('period')
            ->get();

        $byDay = [
            'Sunday' => ['arabic' => 'الأحد', 'periods' => []],
            'Monday' => ['arabic' => 'الإثنين', 'periods' => []],
            'Tuesday' => ['arabic' => 'الثلاثاء', 'periods' => []],
            'Wednesday' => ['arabic' => 'الأربعاء', 'periods' => []],
            'Thursday' => ['arabic' => 'الخميس', 'periods' => []],
        ];

        foreach ($schedules as $s) {
            if (isset($byDay[$s->day])) {
                $byDay[$s->day]['periods'][] = [
                    'period' => $s->period,
                    'subject_name' => $s->subject->name ?? 'حصة',
                    'teacher_name' => $s->teacher->name ?? 'معلم',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $byDay,
        ]);
    }

    // =========================================================================
    // 👨‍🏫 2. واجهات المعلم (Teacher Mobile API)
    // =========================================================================

    /**
     * لوحة تحكم المعلم (Teacher Dashboard)
     */
    public function getTeacherDashboard(Request $request)
    {
        $teacher = $request->user();

        // فصول ومواد المعلم
        $assignments = DB::table('teacher_subject_section')
            ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
            ->join('classes', 'teacher_subject_section.section_id', '=', 'classes.id')
            ->join('grades', 'classes.grade_id', '=', 'grades.id')
            ->where('teacher_subject_section.teacher_id', $teacher->id)
            ->select(
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'grades.name as grade_name',
                'classes.id as class_id',
                'classes.name as class_name',
                'classes.section as class_section'
            )
            ->get();

        $classesData = [];
        foreach ($assignments as $a) {
            $studentCount = StudentProfile::where('class_id', $a->class_id)->count();
            $classesData[] = [
                'class_id' => $a->class_id,
                'class_name' => $a->class_name ?? ($a->grade_name . ' - ' . $a->class_section),
                'grade_name' => $a->grade_name,
                'subject_id' => $a->subject_id,
                'subject_name' => $a->subject_name,
                'students_count' => $studentCount,
            ];
        }

        // جدول اليوم للمعلم
        $daysMap = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
        $todayEnglish = $daysMap[now()->dayOfWeek] ?? 'Sunday';
        $todaySchedule = Schedule::where('teacher_id', $teacher->id)
            ->where('day', $todayEnglish)
            ->with(['subject', 'schoolClass.grade'])
            ->orderBy('period')
            ->get()
            ->map(function ($s) {
                return [
                    'period' => $s->period,
                    'subject_name' => $s->subject->name ?? 'مادة',
                    'class_name' => $s->schoolClass ? ($s->schoolClass->grade->name . ' - ' . $s->schoolClass->section) : 'فصل',
                ];
            });

        // الواجبات المضافة مؤخراً بواسطة هذا المعلم
        $homeworks = Homework::where('teacher_id', $teacher->id)
            ->with(['subject', 'schoolClass'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'title' => $h->title,
                    'description' => $h->description,
                    'subject_name' => $h->subject->name ?? '',
                    'class_name' => $h->schoolClass ? ($h->schoolClass->name ?? $h->schoolClass->section) : '',
                    'due_date' => $h->due_date,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'school_name' => $teacher->school->name ?? 'المدرسة الذكية',
                ],
                'classes' => $classesData,
                'today_schedule' => $todaySchedule,
                'recent_homeworks' => $homeworks,
            ]
        ]);
    }

    /**
     * قائمة طلاب الفصل لتسجيل الحضور أو رصد الدرجات
     */
    public function getTeacherClassStudents(Request $request, $classId)
    {
        $students = StudentProfile::where('class_id', $classId)
            ->with('user')
            ->get();

        $date = $request->query('date', today()->toDateString());

        // جلب حضور اليوم إن وجد
        $attendances = Attendance::where('class_id', $classId)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        $result = [];
        foreach ($students as $stu) {
            $user = $stu->user;
            if (!$user) continue;

            $att = $attendances->get($stu->id);
            $result[] = [
                'student_profile_id' => $stu->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'student_number' => $stu->student_number ?? 'STU-' . str_pad($stu->id, 4, '0', STR_PAD_LEFT),
                'photo' => $user->photo,
                'today_status' => $att ? $att->status : 'present', // افتراضياً حاضر
                'today_note' => $att ? $att->note : null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * تسجيل حضور وغياب الفصل من هاتف المعلم
     */
    public function saveTeacherAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'attendance_date' => 'required|date',
            'records' => 'required|array',
            'records.*.student_profile_id' => 'required',
            'records.*.user_id' => 'required',
            'records.*.status' => 'required|in:present,absent,late',
        ]);

        foreach ($request->records as $rec) {
            Attendance::updateOrCreate(
                [
                    'class_id' => $request->class_id,
                    'student_id' => $rec['student_profile_id'],
                    'attendance_date' => $request->attendance_date,
                ],
                [
                    'user_id' => $rec['user_id'],
                    'status' => $rec['status'],
                    'note' => $rec['note'] ?? null,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم حفظ سجل الحضور والغياب بنجاح ✅',
        ]);
    }

    /**
     * إضافة واجب مدرسي جديد من هاتف المعلم
     */
    public function storeTeacherHomework(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
        ]);

        $hw = Homework::create([
            'teacher_id' => $request->user()->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم نشر الواجب المدرسي بنجاح ✅',
            'data' => $hw,
        ]);
    }

    // =========================================================================
    // 🛡️ SafeBox & Wellbeing Methods (صندوق الأمان المدرسي)
    // =========================================================================

    /**
     * إرسال نبض المشاعر اليومي للطالب
     */
    public function submitStudentMoodPulse(Request $request)
    {
        $request->validate([
            'mood' => 'required|string',
            'emoji' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $user = $request->user();
        $schoolId = $user->school_id ?? 1;

        $pulse = StudentWellbeingPulse::create([
            'school_id' => $schoolId,
            'student_id' => $user->id,
            'mood' => $request->mood,
            'emoji' => $request->emoji ?? '😊',
            'note' => $request->note,
            'week_date' => today(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'شكراً لمشاركتك! نحن هنا لدعمك دائماً 💙',
            'data' => $pulse,
        ]);
    }

    /**
     * تقديم بلاغ سري عن تنمر أو مشكلة
     */
    public function submitSafeBoxReport(Request $request)
    {
        $request->validate([
            'category' => 'required|string|in:bullying,academic,psychological,family,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'urgency' => 'nullable|string|in:low,medium,high,critical',
        ]);

        $user = $request->user();
        $schoolId = $user->school_id ?? 1;

        $report = SafeBoxReport::create([
            'school_id' => $schoolId,
            'student_id' => $user->id,
            'token' => Str::random(10),
            'category' => $request->category,
            'title' => $request->title,
            'description' => $request->description,
            'urgency' => $request->urgency ?? 'medium',
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم استلام بلاغك بسرية تامة وسيتم مراجعته من قبل الأخصائي الاجتماعي بعناية 🛡️',
            'data' => $report,
        ]);
    }

    /**
     * استعراض بلاغات الطالب وردود الأخصائي عليها
     */
    public function getStudentSafeBoxReports(Request $request)
    {
        $user = $request->user();
        $reports = SafeBoxReport::where('student_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    // =========================================================================
    // 📝 Teacher Marks & Assessments Methods (رصد درجات المعلم)
    // =========================================================================

    /**
     * جلب التقييمات والاختبارات لمادة وفصل معين
     */
    public function getTeacherAssessments(Request $request, $classId, $subjectId)
    {
        $assessments = Assessment::where('subject_id', $subjectId)
            ->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $assessments,
        ]);
    }

    /**
     * إضافة تقييم / اختبار جديد للفصل
     */
    public function createTeacherAssessment(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'class_id' => 'required',
            'name' => 'required|string|max:255',
            'max_score' => 'required|numeric|min:1',
            'type' => 'nullable|string',
            'semester' => 'nullable|in:1,2',
        ]);

        $assessment = Assessment::create([
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,
            'name' => $request->name,
            'title' => $request->name,
            'max_score' => $request->max_score,
            'type' => $request->type ?? 'activity',
            'semester' => $request->semester ?? 1,
            'date' => today(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء التقييم بنجاح ✅',
            'data' => $assessment,
        ]);
    }

    /**
     * جلب درجات طلاب الفصل لتقييم معين
     */
    public function getAssessmentMarks(Request $request, $assessmentId)
    {
        $assessment = Assessment::findOrFail($assessmentId);
        $classId = $assessment->class_id;

        $students = StudentProfile::when($classId, function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })->with('user')->get();

        $existingMarks = AssessmentMark::where('assessment_id', $assessmentId)
            ->get()
            ->keyBy('student_id');

        $result = [];
        foreach ($students as $stu) {
            $user = $stu->user;
            if (!$user) continue;

            $markRecord = $existingMarks->get($stu->id) ?? $existingMarks->get($user->id);
            $result[] = [
                'student_profile_id' => $stu->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'student_number' => $stu->student_number ?? 'STU-' . str_pad($stu->id, 4, '0', STR_PAD_LEFT),
                'score' => $markRecord ? (float)$markRecord->score : null,
                'note' => $markRecord ? $markRecord->note : null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'assessment' => $assessment,
                'students' => $result,
            ]
        ]);
    }

    /**
     * حفظ واعتماد درجات الطلاب لتقييم معين
     */
    public function saveAssessmentMarks(Request $request, $assessmentId)
    {
        $request->validate([
            'marks' => 'required|array',
            'marks.*.student_profile_id' => 'required',
            'marks.*.score' => 'required|numeric|min:0',
        ]);

        foreach ($request->marks as $m) {
            AssessmentMark::updateOrCreate(
                [
                    'assessment_id' => $assessmentId,
                    'student_id' => $m['student_profile_id'],
                ],
                [
                    'score' => $m['score'],
                    'note' => $m['note'] ?? null,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم حفظ درجات الطلاب بنجاح ✅',
        ]);
    }

    // =========================================================================
    // 👔 Manager & School Admin Methods (لوحة قيادة المدير ومسؤول النظام)
    // =========================================================================

    public function getManagerDashboard(Request $request)
    {
        $schoolId = $request->user()->school_id ?? 1;

        $studentsCount = User::where('role', 'student')->where('school_id', $schoolId)->count();
        $teachersCount = User::where('role', 'teacher')->where('school_id', $schoolId)->count();
        $classesCount  = SchoolClass::where('school_id', $schoolId)->count();

        // مؤشر حضور اليوم
        $today = now()->format('Y-m-d');
        $todayAttendances = Attendance::where('attendance_date', $today)
            ->whereHas('user', fn($q) => $q->where('school_id', $schoolId))
            ->get();
        
        $todayTotal = $todayAttendances->count();
        $todayPresent = $todayAttendances->filter(function($item) {
            return in_array($item->status, ['1', 1, 'present', 'موجود', 'حاضر']);
        })->count();

        $todayAttendanceRate = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 1) : 96.5;

        // المؤشرات المالية
        $collectedFees = (float) FeePayment::whereHas('student.user', fn($q) => $q->where('school_id', $schoolId))->sum('amount');
        $totalExpenses = (float) Expense::where('school_id', $schoolId)->sum('amount');
        $netBalance = $collectedFees - $totalExpenses;

        $pendingLeavesCount = LeaveRequest::where('status', 'pending')
            ->whereHas('student.user', fn($q) => $q->where('school_id', $schoolId))
            ->count();

        $pendingSafeBoxCount = SafeBoxReport::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->count();

        // أحدث طلبات الاستئذان
        $recentLeaves = LeaveRequest::with(['student.user', 'parent'])
            ->whereHas('student.user', fn($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->take(5)
            ->get()
            ->map(function($l) {
                return [
                    'id' => $l->id,
                    'student_name' => $l->student->user->name ?? 'طالب',
                    'parent_name' => $l->parent->name ?? 'ولي أمر',
                    'leave_date' => $l->leave_date,
                    'type' => $l->type,
                    'reason' => $l->reason,
                    'status' => $l->status,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => [
                    'students_count' => $studentsCount,
                    'teachers_count' => $teachersCount,
                    'classes_count' => $classesCount,
                    'attendance_rate' => $todayAttendanceRate,
                    'collected_fees' => $collectedFees,
                    'total_expenses' => $totalExpenses,
                    'net_balance' => $netBalance,
                    'pending_leaves_count' => $pendingLeavesCount,
                    'pending_safebox_count' => $pendingSafeBoxCount,
                ],
                'recent_leaves' => $recentLeaves,
            ]
        ]);
    }

    public function getManagerLeaves(Request $request)
    {
        $schoolId = $request->user()->school_id ?? 1;

        $leaves = LeaveRequest::with(['student.user', 'parent'])
            ->whereHas('student.user', fn($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->get()
            ->map(function($l) {
                return [
                    'id' => $l->id,
                    'student_name' => $l->student->user->name ?? 'طالب',
                    'parent_name' => $l->parent->name ?? 'ولي أمر',
                    'leave_date' => $l->leave_date,
                    'type' => $l->type,
                    'reason' => $l->reason,
                    'status' => $l->status,
                    'created_at' => $l->created_at ? $l->created_at->format('Y-m-d H:i') : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $leaves,
        ]);
    }

    public function actionManagerLeave(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => $request->status === 'approved' ? 'تمت الموافقة على طلب الاستئذان ✅' : 'تم رفض طلب الاستئذان',
        ]);
    }

    public function getManagerSafeBoxReports(Request $request)
    {
        $schoolId = $request->user()->school_id ?? 1;

        $reports = SafeBoxReport::where('school_id', $schoolId)
            ->with('student')
            ->latest()
            ->get()
            ->map(function($r) {
                return [
                    'id' => $r->id,
                    'student_name' => $r->student->name ?? 'طالب غير معلن',
                    'category' => $r->category,
                    'title' => $r->title,
                    'description' => $r->description,
                    'urgency' => $r->urgency,
                    'status' => $r->status,
                    'admin_notes' => $r->admin_notes,
                    'created_at' => $r->created_at ? $r->created_at->format('Y-m-d H:i') : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    public function respondManagerSafeBoxReport(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_progress,resolved,closed',
            'admin_notes' => 'nullable|string',
        ]);

        $report = SafeBoxReport::findOrFail($id);
        $report->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة البلاغ بنجاح ✅',
        ]);
    }

    public function getManagerAiAdvisor(Request $request)
    {
        $schoolId = $request->user()->school_id ?? 1;
        $aiService = new AiEducationService();
        $atRisk = $aiService->analyzeAcademicRisk($schoolId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'at_risk_students' => $atRisk,
                'advisor_summary' => [
                    'attendance_advice' => 'نسبة الحضور ممتازة إجمالاً، يُنصح بمتابعة الطلاب المتأخرين صباحاً لتعزيز الانضباط.',
                    'academic_advice' => 'لوحظ تحسن ملحوظ في معدلات مادتي الرياضيات والعلوم لهذا الشهر.',
                    'early_warning_count' => count($atRisk),
                ]
            ]
        ]);
    }

    // =========================================================================
    // 💳 Parent Fees & Invoices (الرسوم المدرسية لولي الأمر)
    // =========================================================================

    public function getParentFees(Request $request, $studentId = null)
    {
        $parent = $request->user();

        $studentsQuery = StudentProfile::whereIn('user_id', function ($q) use ($parent) {
            $q->select('student_id')->from('parent_student')->where('parent_id', $parent->id);
        })->with(['user', 'feePayments']);

        if ($studentId) {
            $studentsQuery->where('id', $studentId)->orWhere('user_id', $studentId);
        }

        $students = $studentsQuery->get();

        $result = $students->map(function ($stu) {
            $total = (float)($stu->total_fees ?? 3500);
            $paid = (float)($stu->paid_fees ?? $stu->feePayments->sum('amount'));
            $remaining = max(0, $total - $paid);

            $payments = $stu->feePayments->map(function ($p) {
                return [
                    'id' => $p->id,
                    'amount' => (float)$p->amount,
                    'payment_date' => $p->payment_date,
                    'receipt_number' => $p->receipt_number ?? 'REC-' . $p->id,
                    'notes' => $p->notes ?? 'دفعة قسط دراسي',
                ];
            });

            return [
                'student_profile_id' => $stu->id,
                'student_name' => $stu->user->name ?? 'الطالب',
                'student_number' => $stu->student_number ?? 'STU-' . $stu->id,
                'total_fees' => $total,
                'paid_fees' => $paid,
                'remaining_fees' => $remaining,
                'payment_percentage' => $total > 0 ? round(($paid / $total) * 100, 1) : 0,
                'payments' => $payments,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    // =========================================================================
    // 📝 Student Online Exams & Quizzes (الاختبارات الإلكترونية للطلاب)
    // =========================================================================

    public function getStudentOnlineExams(Request $request)
    {
        $user = $request->user();
        $studentProfile = StudentProfile::where('user_id', $user->id)->first();
        $classId = $studentProfile->class_id ?? null;

        if (!$classId) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $exams = OnlineExam::with(['subject', 'teacher'])
            ->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            })
            ->where('is_active', true)
            ->latest()
            ->get();

        $result = $exams->map(function ($e) use ($user) {
            $attempt = OnlineExamAttempt::where('online_exam_id', $e->id)
                ->where('student_id', $user->id)
                ->first();

            return [
                'id' => $e->id,
                'title' => $e->title,
                'subject_name' => $e->subject->name ?? 'مادة عامة',
                'teacher_name' => $e->teacher->name ?? 'المعلم',
                'duration_minutes' => $e->duration_minutes ?? 30,
                'total_questions' => $e->questions()->count(),
                'total_score' => $e->total_score ?? 10,
                'has_attempted' => $attempt !== null,
                'my_score' => $attempt ? $attempt->total_score : null,
                'attempt_date' => $attempt ? $attempt->created_at->format('Y-m-d') : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function getOnlineExamForTaking(Request $request, $id)
    {
        $user = $request->user();
        $exam = OnlineExam::with(['subject', 'questions'])->findOrFail($id);

        $attempt = OnlineExamAttempt::where('online_exam_id', $id)
            ->where('student_id', $user->id)
            ->first();

        if ($attempt) {
            return response()->json([
                'status' => 'error',
                'message' => 'لقد قمت بأداء هذا الاختبار مسبقاً!',
                'score' => $attempt->total_score,
            ], 400);
        }

        $questions = $exam->questions->map(function ($q) {
            $options = is_array($q->options) ? $q->options : (json_decode($q->options, true) ?? []);
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'options' => $options,
                'score' => $q->score ?? 1,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject_name' => $exam->subject->name ?? 'المادة',
                'duration_minutes' => $exam->duration_minutes ?? 30,
                'questions' => $questions,
            ]
        ]);
    }

    public function submitOnlineExamAttempt(Request $request, $id)
    {
        $user = $request->user();
        $exam = OnlineExam::with('questions')->findOrFail($id);

        if (OnlineExamAttempt::where('online_exam_id', $id)->where('student_id', $user->id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'تم استلام إجاباتك مسبقاً لهذا الاختبار.'], 400);
        }

        $answers = $request->input('answers', []);
        $score = 0;
        $maxScore = 0;
        $studentAnswers = [];

        foreach ($exam->questions as $question) {
            $maxScore += ($question->score ?? 1);
            $userAnswer = $answers['q_' . $question->id] ?? $answers[$question->id] ?? null;
            $isCorrect = false;

            if ($userAnswer !== null && trim(strtolower($userAnswer)) === trim(strtolower($question->correct_answer))) {
                $score += ($question->score ?? 1);
                $isCorrect = true;
            }

            $studentAnswers[$question->id] = [
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
            ];
        }

        OnlineExamAttempt::create([
            'online_exam_id' => $exam->id,
            'student_id' => $user->id,
            'student_answers' => $studentAnswers,
            'total_score' => $score,
            'started_at' => now()->subMinutes(15),
            'finished_at' => now(),
            'status' => 'completed',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "تم تسليم الاختبار بنجاح! درجتك المحققة: $score من $maxScore",
            'data' => [
                'score' => $score,
                'max_score' => $maxScore,
                'percentage' => $maxScore > 0 ? round(($score / $maxScore) * 100, 1) : 0,
            ]
        ]);
    }

    // =========================================================================
    // 🌟 Student Portfolio (ملف إنجاز الطالب الرقمي الموثق)
    // =========================================================================

    public function getStudentPortfolio(Request $request, $studentId = null)
    {
        $targetUserId = $studentId ?? $request->user()->id;
        $user = User::find($targetUserId) ?? $request->user();

        $portfolio = StudentPortfolio::firstOrCreate(
            ['user_id' => $targetUserId],
            [
                'school_id' => $user->school_id ?? 1,
                'slug' => Str::slug($user->name . '-' . Str::random(5)),
                'headline' => 'طالب متميز وشغوف بالتعلم',
                'about_me' => 'أعمل على تنمية مهاراتي الأكاديمية والمشاركة في الأنشطة المدرسية والابتكار.',
                'skills' => 'القيادة, العمل الجماعي, البرمجة, حل المشكلات',
                'volunteer_hours' => 25,
                'is_public' => true,
                'verified_by_school' => true,
                'verification_seal_number' => 'VER-' . strtoupper(Str::random(8)),
            ]
        );

        $items = StudentPortfolioItem::where('student_portfolio_id', $portfolio->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'portfolio' => $portfolio,
                'items' => $items,
                'public_url' => url('/portfolio/' . $portfolio->slug),
            ]
        ]);
    }

    public function addStudentPortfolioItem(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'year' => 'nullable|string',
        ]);

        $portfolio = StudentPortfolio::where('user_id', $request->user()->id)->first();
        if (!$portfolio) {
            $portfolio = StudentPortfolio::create([
                'user_id' => $request->user()->id,
                'school_id' => $request->user()->school_id ?? 1,
                'slug' => Str::slug($request->user()->name . '-' . Str::random(5)),
            ]);
        }

        $item = StudentPortfolioItem::create([
            'student_portfolio_id' => $portfolio->id,
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'year' => $request->year ?? date('Y'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة الإنجاز إلى ملفك الرقمي بنجاح 🌟',
            'data' => $item,
        ]);
    }

    // =========================================================================
    // 🤖 Teacher AI Quiz Assistant & Official Letters
    // =========================================================================

    public function generateTeacherAiQuiz(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
            'count' => 'nullable|integer|min:1|max:10',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
        ]);

        $aiService = new AiEducationService();
        $questions = $aiService->generateQuestions(
            $request->topic,
            $request->subject_name,
            (int)($request->count ?? 5),
            $request->difficulty ?? 'medium'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم توليد أسئلة الاختبار بنجاح بواسطة الذكاء الاصطناعي 🤖',
            'data' => $questions,
        ]);
    }

    public function getTeacherOfficialLetters(Request $request)
    {
        $user = $request->user();

        $letters = OfficialLetter::where('recipient_id', $user->id)
            ->with('sender')
            ->latest()
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'content' => $l->content,
                    'type' => $l->type,
                    'reference_no' => $l->reference_no,
                    'sender_name' => $l->sender->name ?? 'إدارة المدرسة',
                    'date' => $l->created_at ? $l->created_at->format('Y-m-d') : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $letters,
        ]);
    }

    // =========================================================================
    // 💬 School Chat & In-App Messaging
    // =========================================================================

    public function getChatContacts(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id ?? 1;

        $contacts = User::where('school_id', $schoolId)
            ->where('id', '!=', $user->id)
            ->whereIn('role', ['admin', 'manager', 'teacher', 'parent', 'student'])
            ->select('id', 'name', 'email', 'role')
            ->take(30)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $contacts,
        ]);
    }

    public function getChatMessages(Request $request, $userId)
    {
        $authId = $request->user()->id;

        $messages = Message::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $authId);
        })
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($m) use ($authId) {
            return [
                'id' => $m->id,
                'message' => $m->message,
                'is_me' => $m->sender_id == $authId,
                'time' => $m->created_at ? $m->created_at->format('H:i') : null,
                'date' => $m->created_at ? $m->created_at->format('Y-m-d') : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $messages,
        ]);
    }

    public function sendChatMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'is_me' => true,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('Y-m-d'),
            ]
        ]);
    }
}
