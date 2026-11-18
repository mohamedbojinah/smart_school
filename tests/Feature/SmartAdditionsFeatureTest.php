<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\FeePayment;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\Attendance;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmartAdditionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $student;
    protected $teacher;
    protected $admin;
    protected $parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'id' => 1,
            'name' => 'مدرسة التميز الذكية',
            'academic_year' => '2026/2027',
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
        ]);
        $this->student->assignRole('student');

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'teacher',
        ]);
        $this->teacher->assignRole('teacher');

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'admin',
        ]);
        $this->admin->assignRole('admin');

        $this->parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'parent',
        ]);
        $this->parent->assignRole('parent');
    }

    public function test_public_document_verification_for_receipt_and_certificate()
    {
        $profile = StudentProfile::create([
            'user_id' => $this->student->id,
            'wallet_balance' => 0,
        ]);

        $payment = FeePayment::create([
            'student_id' => $profile->id,
            'amount' => 500,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_number' => 'RCP-TEST-999',
        ]);

        $validUrl = QrCodeService::getVerificationUrl('receipt', $payment->id);
        $response = $this->get($validUrl);
        $response->assertStatus(200);
        $response->assertSee('RCP-TEST-999');
        $response->assertSee('وثيقة رسمية صحيحة ومعتمدة 100%');

        // Test certificate verification
        $certUrl = QrCodeService::getVerificationUrl('certificate', $this->student->id);
        $response = $this->get($certUrl);
        $response->assertStatus(200);
        $response->assertSee($this->student->name);

        // Test non-existent document
        $response = $this->get('/verify/document/receipt/999999');
        $response->assertSee('عذراً، تعذر العثور على الوثيقة المطلوبة');
    }

    public function test_student_digital_badge_generation_and_access_control()
    {
        $profile = StudentProfile::create([
            'user_id' => $this->student->id,
            'wallet_balance' => 0,
        ]);

        // Student viewing their own badge
        $response = $this->actingAs($this->student)->get(route('student.badge.show'));
        $response->assertStatus(200);
        $response->assertSee($this->student->name);
        $response->assertSee('STD-' . str_pad($this->student->id, 5, '0', STR_PAD_LEFT));

        // Parent authorized to view child badge
        $this->parent->children()->attach($this->student->id);
        $response = $this->actingAs($this->parent)->get(route('student.badge.show', $this->student->id));
        $response->assertStatus(200);

        // Stranger parent should be forbidden (403)
        $strangerParent = User::factory()->create(['school_id' => $this->school->id, 'role' => 'parent']);
        $strangerParent->assignRole('parent');
        $response = $this->actingAs($strangerParent)->get(route('student.badge.show', $this->student->id));
        $response->assertStatus(403);
    }

    public function test_qr_attendance_scan_marks_student_present()
    {
        $profile = StudentProfile::create([
            'user_id' => $this->student->id,
            'wallet_balance' => 0,
        ]);

        // Scan by teacher
        $response = $this->actingAs($this->teacher)->postJson(route('attendance.scan_qr'), [
            'code' => "STD-{$this->student->id}"
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_registered' => false,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'attendance_date' => date('Y-m-d'),
            'status' => 'present',
        ]);

        // Duplicate scan on same day returns already_registered: true
        $response = $this->actingAs($this->teacher)->postJson(route('attendance.scan_qr'), [
            'code' => (string)$this->student->id
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_registered' => true,
        ]);
    }

    public function test_ai_question_generation_and_exam_saving()
    {
        // 1. Generate questions
        $response = $this->actingAs($this->teacher)->postJson(route('teacher.ai.generate'), [
            'subject_name' => 'العلوم',
            'topic' => 'دورة الماء في الطبيعة',
            'count' => 3,
            'difficulty' => 'easy',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'count',
            'questions' => [
                '*' => ['question_text', 'options', 'correct_answer', 'score']
            ]
        ]);

        $questions = $response->json('questions');
        $this->assertCount(3, $questions);

        // 2. Save questions to an exam
        $grade = Grade::create(['name' => 'الثاني المتوسط', 'school_id' => $this->school->id]);
        $class = SchoolClass::create(['name' => '2/أ', 'section' => 'أ', 'grade_id' => $grade->id, 'school_id' => $this->school->id]);
        $subject = Subject::create(['name' => 'العلوم', 'grade_id' => $grade->id, 'school_id' => $this->school->id]);

        $exam = OnlineExam::create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'title' => 'اختبار العلوم الذكي',
            'duration_minutes' => 20,
        ]);

        $saveResponse = $this->actingAs($this->teacher)->postJson(route('teacher.ai.save_to_exam'), [
            'online_exam_id' => $exam->id,
            'questions' => $questions,
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('online_exam_questions', [
            'online_exam_id' => $exam->id,
            'question_text' => $questions[0]['question_text'],
        ]);
    }

    public function test_ai_early_warning_dashboard_access()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ai.early_warning'));
        $response->assertStatus(200);
        $response->assertSee('نظام الإنذار المبكر الذكي');
    }
}
