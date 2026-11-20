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
}
