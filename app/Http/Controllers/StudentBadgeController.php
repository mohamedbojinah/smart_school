<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use App\Services\QrCodeService;

class StudentBadgeController extends Controller
{
    /**
     * Show digital printable student ID badge.
     */
    public function show(Request $request, $id = null)
    {
        $studentId = $id;

        // If no ID provided and user is student, show their own badge
        if (!$studentId && auth()->check() && auth()->user()->role === 'student') {
            $studentId = auth()->id();
        }

        if (!$studentId) {
            abort(404, 'يرجى تحديد الطالب.');
        }

        $student = User::with(['studentProfile.schoolClass.grade', 'school'])->findOrFail($studentId);

        // Security check: if parent is logged in, ensure student is their child
        if (auth()->check() && auth()->user()->role === 'parent') {
            $isChild = auth()->user()->children()->where('student_id', $student->id)->exists();
            if (!$isChild) {
                abort(403, 'غير مصرح لك بعرض بطاقة هذا الطالب.');
            }
        }

        // Security check: if teacher/admin is logged in, ensure same school
        if (auth()->check() && auth()->user()->school_id && $student->school_id != auth()->user()->school_id) {
            abort(403, 'الطالب لا ينتمي لمدرستك.');
        }

        $school = $student->school ?? School::first();
        $profile = $student->studentProfile;
        $class = $profile ? $profile->schoolClass : null;

        // Verification URL and raw code for QR
        $verificationUrl = QrCodeService::getVerificationUrl('badge', $student->id);
        $checkinCode = "STD-{$student->id}";

        return view('student.badge', compact('student', 'school', 'profile', 'class', 'verificationUrl', 'checkinCode'));
    }
}
