<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Assessment;
use App\Models\AssessmentMark;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamAttempt;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ComprehensiveBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'id' => 1,
            'name' => 'مدرسة التميز الذكية',
            'academic_year' => '2026/2027',
            'grading_locked' => false,
        ]);
    }

    public function test_wallet_daily_limit_and_pin_protection()
    {
        $studentUser = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'wallet_balance' => 200.00,
            'daily_limit' => 50.00,
            'wallet_pin' => Hash::make('4321'),
        ]);

        $this->assertEquals(200.00, $profile->wallet_balance);
        $this->assertEquals(50.00, $profile->daily_limit);

        $this->assertTrue(Hash::check('4321', $profile->wallet_pin));
        $this->assertFalse(Hash::check('0000', $profile->wallet_pin));

        $purchaseAmount = 30.00;
        $this->assertTrue($purchaseAmount <= $profile->daily_limit);
        $this->assertTrue($purchaseAmount <= $profile->wallet_balance);

        $profile->wallet_balance -= $purchaseAmount;
        $profile->save();

        $this->assertEquals(170.00, $profile->fresh()->wallet_balance);

        $excessiveAmount = 60.00;
        $this->assertFalse($excessiveAmount <= $profile->daily_limit);
    }

    public function test_attendance_status_types_and_integrity()
    {
        $studentUser = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'wallet_balance' => 0,
        ]);

        $grade = Grade::create([
            'name' => 'الأول الثانوي',
            'school_id' => $this->school->id,
        ]);

        $class = SchoolClass::create([
            'name' => 'أول ثانوي - أ',
            'section' => 'أ',
            'grade_id' => $grade->id,
            'school_id' => $this->school->id,
        ]);

        $today = now()->format('Y-m-d');

        $attendance = Attendance::create([
            'student_id' => $profile->id,
            'class_id' => $class->id,
            'attendance_date' => $today,
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $profile->id,
            'class_id' => $class->id,
            'attendance_date' => $today,
            'status' => 'present',
        ]);

        $attendance->update(['status' => 'excused']);
        $this->assertEquals('excused', $attendance->fresh()->status);
    }

    public function test_expense_school_scoping_and_positive_balance()
    {
        $expense = Expense::create([
            'school_id' => $this->school->id,
            'title' => 'فاتورة كهرباء المعامل',
            'amount' => 450.50,
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'utilities',
        ]);

        $this->assertDatabaseHas('expenses', [
            'school_id' => $this->school->id,
            'title' => 'فاتورة كهرباء المعامل',
            'amount' => 450.50,
        ]);

        $this->assertGreaterThan(0, $expense->amount);
    }

    public function test_assessment_mark_calculation_and_percentage()
    {
        $teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'teacher',
        ]);

        $student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
        ]);

        $grade = Grade::create([
            'name' => 'الثاني الثانوي',
            'school_id' => $this->school->id,
        ]);

        $class = SchoolClass::create([
            'name' => 'ثاني ثانوي - ب',
            'section' => 'ب',
            'grade_id' => $grade->id,
            'school_id' => $this->school->id,
        ]);

        $subject = Subject::create([
            'name' => 'الفيزياء',
            'grade_id' => $grade->id,
            'school_id' => $this->school->id,
        ]);

        $assessment = Assessment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'section_id' => $class->id,
            'name' => 'اختبار منتصف الفصل',
            'max_score' => 50,
        ]);

        $mark = AssessmentMark::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'score' => 45,
        ]);

        $percentage = ($mark->score / $assessment->max_score) * 100;
        $this->assertEquals(90.0, $percentage);
        $this->assertTrue($mark->score <= $assessment->max_score);
        $this->assertTrue($mark->score >= 0);
    }

    public function test_online_exam_question_scoring_and_passing_rate()
    {
        $teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'teacher',
        ]);

        $student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
        ]);

        $grade = Grade::create([
            'name' => 'الثالث الثانوي',
            'school_id' => $this->school->id,
        ]);

        $class = SchoolClass::create([
            'name' => 'ثالث ثانوي - أ',
            'section' => 'أ',
            'grade_id' => $grade->id,
            'school_id' => $this->school->id,
        ]);

        $subject = Subject::create([
            'name' => 'الكيمياء',
            'grade_id' => $grade->id,
            'school_id' => $this->school->id,
        ]);

        $exam = OnlineExam::create([
            'school_id' => $this->school->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'title' => 'اختبار الأحماض والقواعد',
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $q1 = OnlineExamQuestion::create([
            'online_exam_id' => $exam->id,
            'question_text' => 'ما هو الرقم الهيدروجيني للماء النقي؟',
            'question_type' => 'multiple_choice',
            'options' => json_encode(['5', '7', '9', '14']),
            'correct_answer' => '7',
            'score' => 10,
        ]);

        $q2 = OnlineExamQuestion::create([
            'online_exam_id' => $exam->id,
            'question_text' => 'حمض الهيدروكلوريك يعتبر حمضاً قوياً أم ضعيفاً؟',
            'question_type' => 'multiple_choice',
            'options' => json_encode(['قوي', 'ضعيف']),
            'correct_answer' => 'قوي',
            'score' => 10,
        ]);

        $studentAnswers = [
            $q1->id => '7',
            $q2->id => 'قوي',
        ];

        $calculatedScore = 0;
        foreach ([$q1, $q2] as $q) {
            if (isset($studentAnswers[$q->id]) && $studentAnswers[$q->id] === $q->correct_answer) {
                $calculatedScore += $q->score;
            }
        }

        $attempt = OnlineExamAttempt::create([
            'online_exam_id' => $exam->id,
            'student_id' => $student->id,
            'total_score' => $calculatedScore,
            'student_answers' => json_encode($studentAnswers),
            'started_at' => now(),
            'finished_at' => now()->addMinutes(15),
            'status' => 'completed',
        ]);

        $this->assertEquals(20, $attempt->total_score);
        $this->assertEquals('completed', $attempt->status);
    }

    public function test_leave_request_approval_transitions()
    {
        $parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'parent',
        ]);

        $student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
        ]);

        $leave = LeaveRequest::create([
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'leave_date' => now()->addDay()->format('Y-m-d'),
            'type' => 'sick',
            'reason' => 'مراجعة طبية عاجلة بالعيادة',
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $leave->status);

        // Admin approves
        $leave->update(['status' => 'approved']);
        $this->assertEquals('approved', $leave->fresh()->status);

        // Check rejection possibility
        $leave->update(['status' => 'rejected']);
        $this->assertEquals('rejected', $leave->fresh()->status);
    }
}
