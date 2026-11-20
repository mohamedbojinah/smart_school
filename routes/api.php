<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Chat API
Route::post('/chat/send', function(Request $request) {
    return response()->json(['status' => 'success']);
});

// Online Exams API
Route::post('/exams/{id}/submit', function(Request $request, $id) {
    return response()->json(['status' => 'success', 'score' => 1]);
});

// Grades API
Route::post('/grades/publish', function(Request $request) {
    return response()->json(['status' => 'success']);
});

// Accounting API
Route::post('/accounting/expenses', function(Request $request) {
    return response()->json(['status' => 'success']);
});

// Admission API
Route::post('/admissions/{id}/approve', function(Request $request, $id) {
    return response()->json(['status' => 'success']);
});

// Mobile App Multi-School API Routes
use App\Http\Controllers\Api\MobileAppApiController;

Route::post('/mobile/login', [MobileAppApiController::class, 'login']);
Route::get('/mobile/schools', [MobileAppApiController::class, 'getSchools']);

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::get('/parent/dashboard', [MobileAppApiController::class, 'getParentDashboard']);
    Route::post('/parent/wallet/update', [MobileAppApiController::class, 'updateWalletSettings']);
    Route::post('/parent/leaves/submit', [MobileAppApiController::class, 'submitLeaveRequest']);
    Route::get('/parent/leaves', [MobileAppApiController::class, 'getLeaveRequests']);
    Route::get('/parent/homeworks', [MobileAppApiController::class, 'getHomeworks']);

    // 🎓 Student Mobile Routes
    Route::get('/student/dashboard', [MobileAppApiController::class, 'getStudentDashboard']);
    Route::get('/student/grades', [MobileAppApiController::class, 'getStudentGrades']);
    Route::get('/student/schedule', [MobileAppApiController::class, 'getStudentSchedule']);
    Route::post('/student/safebox/pulse', [MobileAppApiController::class, 'submitStudentMoodPulse']);
    Route::post('/student/safebox/report', [MobileAppApiController::class, 'submitSafeBoxReport']);
    Route::get('/student/safebox/history', [MobileAppApiController::class, 'getStudentSafeBoxReports']);

    // 👨‍🏫 Teacher Mobile Routes
    Route::get('/teacher/dashboard', [MobileAppApiController::class, 'getTeacherDashboard']);
    Route::get('/teacher/class/{classId}/students', [MobileAppApiController::class, 'getTeacherClassStudents']);
    Route::post('/teacher/attendance/save', [MobileAppApiController::class, 'saveTeacherAttendance']);
    Route::post('/teacher/homeworks/create', [MobileAppApiController::class, 'storeTeacherHomework']);
    Route::get('/teacher/class/{classId}/subject/{subjectId}/assessments', [MobileAppApiController::class, 'getTeacherAssessments']);
    Route::post('/teacher/assessments/create', [MobileAppApiController::class, 'createTeacherAssessment']);
    Route::get('/teacher/assessment/{assessmentId}/marks', [MobileAppApiController::class, 'getAssessmentMarks']);
    Route::post('/teacher/assessment/{assessmentId}/marks/save', [MobileAppApiController::class, 'saveAssessmentMarks']);

    // Notifications & FCM
    Route::post('/fcm-token', [MobileAppApiController::class, 'registerDeviceToken']);
    Route::get('/notifications', [MobileAppApiController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [MobileAppApiController::class, 'markNotificationAsRead']);
    Route::post('/notifications/read-all', [MobileAppApiController::class, 'markAllNotificationsAsRead']);
    Route::post('/notifications/test', [MobileAppApiController::class, 'sendTestNotification']);
});
