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
}
