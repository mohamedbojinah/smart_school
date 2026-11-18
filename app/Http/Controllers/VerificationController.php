<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\FeePayment;
use App\Models\School;
use App\Services\QrCodeService;
use Carbon\Carbon;

class VerificationController extends Controller
{
    /**
     * Public verification endpoint for official school documents.
     */
    public function verify(Request $request, string $type, string $id)
    {
        $signature = $request->query('sig');
        $isValidSig = true;

        if ($signature) {
            $expectedSig = substr(QrCodeService::generateSignature($type, $id), 0, 16);
            $isValidSig = hash_equals($expectedSig, $signature);
        }

        $document = null;
        $title = 'التحقق من الوثيقة الرسمية';

        switch ($type) {
            case 'receipt':
                $payment = FeePayment::with(['student.user.school', 'student.schoolClass.grade'])->find($id);
                if ($payment) {
                    $school = $payment->student->user->school ?? School::first();
                    $student = $payment->student->user;
                    $class = $payment->student->schoolClass;
                    $document = [
                        'type' => 'سند قبض مالي معتمد',
                        'number' => $payment->receipt_number,
                        'date' => Carbon::parse($payment->payment_date)->format('Y/m/d'),
                        'student_name' => $student->name ?? 'غير محدد',
                        'student_national_id' => $student->national_id ?? 'STD-' . ($student->id ?? '000'),
                        'class_name' => ($class->grade->name ?? '') . ' - ' . ($class->name ?? ''),
                        'amount' => number_format($payment->amount, 2) . ' ريال',
                        'notes' => $payment->notes ?: 'سداد رسوم دراسية معتمدة',
                        'school_name' => $school->name ?? 'المدرسة الذكية',
                        'status' => 'معتمد ومسدد',
                        'verified_at' => now()->format('Y/m/d - H:i'),
                    ];
                    $title = 'التحقق من سند القبض: ' . $payment->receipt_number;
                }
                break;

            case 'certificate':
            case 'report':
                $student = User::with(['studentProfile.schoolClass.grade', 'school'])->find($id);
                if ($student && $student->role === 'student') {
                    $profile = $student->studentProfile;
                    $class = $profile->schoolClass ?? null;
                    $school = $student->school ?? School::first();

                    $document = [
                        'type' => 'شهادة / كشف درجات أكاديمي رسمي',
                        'number' => 'CERT-' . date('Y') . '-' . str_pad($student->id, 5, '0', STR_PAD_LEFT),
                        'date' => now()->format('Y/m/d'),
                        'student_name' => $student->name,
                        'student_national_id' => $student->national_id ?? 'STD-' . $student->id,
                        'class_name' => ($class && $class->grade ? $class->grade->name : '') . ' - ' . ($class ? $class->name : ''),
                        'academic_year' => $school->academic_year ?? '2026/2027',
                        'school_name' => $school->name ?? 'المدرسة الذكية',
                        'status' => 'شهادة معتمدة وموثقة في السجل العام',
                        'verified_at' => now()->format('Y/m/d - H:i'),
                    ];
                    $title = 'التحقق من الشهادة الرسمية: ' . $student->name;
                }
                break;

            case 'student':
            case 'badge':
                $student = User::with(['studentProfile.schoolClass.grade', 'school'])->find($id);
                if ($student && $student->role === 'student') {
                    $profile = $student->studentProfile;
                    $class = $profile->schoolClass ?? null;
                    $school = $student->school ?? School::first();

                    $document = [
                        'type' => 'بطاقة تعريف طالب رقمية معتمدة',
                        'number' => 'ID-' . str_pad($student->id, 6, '0', STR_PAD_LEFT),
                        'date' => now()->format('Y/m/d'),
                        'student_name' => $student->name,
                        'student_national_id' => $student->national_id ?? 'STD-' . $student->id,
                        'class_name' => ($class && $class->grade ? $class->grade->name : '') . ' - ' . ($class ? $class->name : ''),
                        'academic_year' => $school->academic_year ?? '2026/2027',
                        'school_name' => $school->name ?? 'المدرسة الذكية',
                        'photo' => $student->photo ? asset($student->photo) : null,
                        'status' => 'طالب مقيد ومنتظم بالدراسة',
                        'verified_at' => now()->format('Y/m/d - H:i'),
                    ];
                    $title = 'بطاقة الطالب الرقمية: ' . $student->name;
                }
                break;
        }

        return view('verify.document', compact('document', 'isValidSig', 'title', 'type', 'id'));
    }
}
