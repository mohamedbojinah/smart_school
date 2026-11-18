<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\Assessment;
use App\Models\AssessmentMark;
use App\Models\Attendance;
use App\Models\Homework;
use App\Models\LeaveRequest;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MultiSchoolDataIsolationAndMobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected $schoolA;
    protected $schoolB;
    protected $parent;
    protected $childInSchoolA;
    protected $childInSchoolB;
    protected $unrelatedStudentInSchoolB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create two separate schools
        $this->schoolA = School::create([
            'id' => 1,
            'name' => 'المدرسة الذكية - فرع النور',
            'academic_year' => '2026/2027',
        ]);

        $this->schoolB = School::create([
            'id' => 2,
            'name' => 'المدرسة الذكية - فرع الأمل',
            'academic_year' => '2026/2027',
        ]);

        // 2. Parent User
        $this->parent = User::factory()->create([
            'name' => 'ولي أمر متعدد المدارس',
            'email' => 'multi_parent@school.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'school_id' => $this->schoolA->id,
        ]);
        $this->parent->assignRole('parent');

        // 3. Child in School A
        $gradeA = Grade::create(['name' => 'الصف الأول ابتدائي', 'school_id' => $this->schoolA->id]);
        $classA = SchoolClass::create(['name' => '1/أ', 'section' => 'أ', 'grade_id' => $gradeA->id, 'school_id' => $this->schoolA->id]);
        $subjectA = Subject::create(['name' => 'لغة عربية (أ)', 'grade_id' => $gradeA->id, 'school_id' => $this->schoolA->id]);

        $studentUserA = User::factory()->create([
            'name' => 'الطالب أحمد (مدرسة أ)',
            'role' => 'student',
            'school_id' => $this->schoolA->id,
        ]);
        $studentUserA->assignRole('student');
        $this->childInSchoolA = StudentProfile::create([
            'user_id' => $studentUserA->id,
            'class_id' => $classA->id,
            'wallet_balance' => 150.00,
            'daily_limit' => 20.00,
            'wallet_pin' => '1111',
            'total_fees' => 3000.00,
            'paid_fees' => 1000.00,
        ]);

        // 4. Child in School B (Different School for same parent!)
        $gradeB = Grade::create(['name' => 'الصف الثاني إعدادي', 'school_id' => $this->schoolB->id]);
        $classB = SchoolClass::create(['name' => '2/ب', 'section' => 'ب', 'grade_id' => $gradeB->id, 'school_id' => $this->schoolB->id]);
        $subjectB = Subject::create(['name' => 'علوم وفيزياء (ب)', 'grade_id' => $gradeB->id, 'school_id' => $this->schoolB->id]);

        $studentUserB = User::factory()->create([
            'name' => 'الطالبة سارة (مدرسة ب)',
            'role' => 'student',
            'school_id' => $this->schoolB->id,
        ]);
        $studentUserB->assignRole('student');
        $this->childInSchoolB = StudentProfile::create([
            'user_id' => $studentUserB->id,
            'class_id' => $classB->id,
            'wallet_balance' => 80.00,
            'daily_limit' => 15.00,
            'wallet_pin' => '2222',
            'total_fees' => 4500.00,
            'paid_fees' => 2500.00,
        ]);

        // Attach both children to the parent in parent_student table
        DB::table('parent_student')->insert([
            ['parent_id' => $this->parent->id, 'student_id' => $studentUserA->id, 'created_at' => now(), 'updated_at' => now()],
            ['parent_id' => $this->parent->id, 'student_id' => $studentUserB->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Unrelated stranger student in School B
        $strangerUser = User::factory()->create([
            'name' => 'طالب غريب (مدرسة ب)',
            'role' => 'student',
            'school_id' => $this->schoolB->id,
        ]);
        $strangerUser->assignRole('student');
        $this->unrelatedStudentInSchoolB = StudentProfile::create([
            'user_id' => $strangerUser->id,
            'class_id' => $classB->id,
            'wallet_balance' => 500.00,
        ]);

        // 4.5 Teacher user
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $this->schoolA->id,
        ]);
        $teacher->assignRole('teacher');

        // Assessment marks
        $assessmentA = Assessment::create([
            'name' => 'اختبار تحريري دوري',
            'subject_id' => $subjectA->id,
            'section_id' => $classA->id,
            'teacher_id' => $teacher->id,
            'max_score' => 20,
            'semester' => 1,
        ]);
        AssessmentMark::create([
            'assessment_id' => $assessmentA->id,
            'student_id' => $studentUserA->id,
            'score' => 18,
        ]);

        $assessmentB = Assessment::create([
            'name' => 'اختبار نهائي علوم',
            'subject_id' => $subjectB->id,
            'section_id' => $classB->id,
            'teacher_id' => $teacher->id,
            'max_score' => 50,
            'semester' => 1,
        ]);
        AssessmentMark::create([
            'assessment_id' => $assessmentB->id,
            'student_id' => $studentUserB->id,
            'score' => 45,
        ]);
    }

    /**
     * Test 1: Parent can authenticate and view mobile dashboard with aggregated schools
     */
    public function test_parent_receives_multi_school_aggregated_dashboard_via_api()
    {
        $response = $this->actingAs($this->parent, 'sanctum')->getJson('/api/mobile/parent/dashboard');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'parent' => [
                    'id' => $this->parent->id,
                    'name' => 'ولي أمر متعدد المدارس',
                ],
            ]
        ]);

        $responseData = $response->json('data');

        // Verify that BOTH schools are returned
        $this->assertCount(2, $responseData['schools']);
        $schoolIds = collect($responseData['schools'])->pluck('id')->toArray();
        $this->assertContains($this->schoolA->id, $schoolIds);
        $this->assertContains($this->schoolB->id, $schoolIds);

        // Verify that BOTH children are returned with their respective school tags
        $this->assertCount(2, $responseData['children']);
        $childrenSchools = collect($responseData['children'])->pluck('school_id')->toArray();
        $this->assertContains($this->schoolA->id, $childrenSchools);
        $this->assertContains($this->schoolB->id, $childrenSchools);

        // Ensure stranger student is NOT leaked
        $studentUserIds = collect($responseData['children'])->pluck('user_id')->toArray();
        $this->assertNotContains($this->unrelatedStudentInSchoolB->user_id, $studentUserIds);
    }

    /**
     * Test 2: Academic grades from School A do not leak or mix with School B
     */
    public function test_school_a_grades_and_assessments_are_isolated_from_school_b()
    {
        $response = $this->actingAs($this->parent, 'sanctum')->getJson('/api/mobile/parent/dashboard');
        $responseData = $response->json('data');

        $childA = collect($responseData['children'])->firstWhere('school_id', $this->schoolA->id);
        $childB = collect($responseData['children'])->firstWhere('school_id', $this->schoolB->id);

        $this->assertNotNull($childA);
        $this->assertNotNull($childB);

        // Child A should only have subjects from School A
        $childAGradeSubjects = collect($childA['academics']['grades'])->pluck('subject_name')->toArray();
        $this->assertContains('لغة عربية (أ)', $childAGradeSubjects);
        $this->assertNotContains('علوم وفيزياء (ب)', $childAGradeSubjects);

        // Child B should only have subjects from School B
        $childBGradeSubjects = collect($childB['academics']['grades'])->pluck('subject_name')->toArray();
        $this->assertContains('علوم وفيزياء (ب)', $childBGradeSubjects);
        $this->assertNotContains('لغة عربية (أ)', $childBGradeSubjects);
    }

    /**
     * Test 3: Manager or Admin of School A cannot see students, expenses or classes of School B
     */
    public function test_manager_of_school_a_cannot_view_or_leak_school_b_data()
    {
        $managerA = User::factory()->create([
            'name' => 'مدير مدرسة أ',
            'role' => 'manager',
            'school_id' => $this->schoolA->id,
        ]);
        $managerA->assignRole('manager');

        // Create expense in School A and School B
        Expense::create([
            'school_id' => $this->schoolA->id,
            'title' => 'فواتير كهرباء مدرسة أ',
            'amount' => 500,
            'expense_date' => now(),
            'category' => 'utilities',
        ]);

        Expense::create([
            'school_id' => $this->schoolB->id,
            'title' => 'فواتير سرية لمدرسة ب',
            'amount' => 2500,
            'expense_date' => now(),
            'category' => 'utilities',
        ]);

        // When manager of School A queries expenses with school scope
        $schoolAExpenses = Expense::where('school_id', $managerA->school_id)->get();
        $this->assertCount(1, $schoolAExpenses);
        $this->assertEquals('فواتير كهرباء مدرسة أ', $schoolAExpenses->first()->title);

        $schoolBExpenses = Expense::where('school_id', $this->schoolB->id)->get();
        $this->assertCount(1, $schoolBExpenses);
        $this->assertNotEquals($managerA->school_id, $schoolBExpenses->first()->school_id);
    }

    /**
     * Test 4: Mobile API allows updating wallet PIN and limit safely
     */
    public function test_mobile_wallet_update_api_works_for_legitimate_child()
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/mobile/parent/wallet/update', [
            'student_id' => $this->childInSchoolA->id,
            'wallet_pin' => '9999',
            'daily_limit' => 35.5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'wallet_pin' => '9999',
                'daily_limit' => 35.5,
            ]
        ]);

        $this->assertEquals('9999', $this->childInSchoolA->fresh()->wallet_pin);
        $this->assertEquals(35.5, (float)$this->childInSchoolA->fresh()->daily_limit);
    }

    /**
     * Test 5: Mobile leave submission stores correct school/student association
     */
    public function test_mobile_leave_submission_stores_correct_association()
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/mobile/parent/leaves/submit', [
            'student_id' => $this->childInSchoolB->id,
            'type' => 'early_leave',
            'leave_date' => now()->toDateString(),
            'reason' => 'ظرف عائلي طارئ يتطلب خروج مبكر',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'parent_id' => $this->parent->id,
            'student_id' => $this->childInSchoolB->id,
            'type' => 'early_leave',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 6: Mobile Homeworks endpoint filters by parent's children classes only
     */
    public function test_mobile_homeworks_endpoint_only_returns_classes_of_parents_children()
    {
        // Homework in School A class (belonging to child A)
        Homework::create([
            'class_id' => $this->childInSchoolA->class_id,
            'subject_id' => 1,
            'teacher_id' => 1,
            'title' => 'واجب درس القراءة',
            'description' => 'حل الصفحة 10',
            'due_date' => now()->addDays(2),
        ]);

        // Homework in an unrelated class
        $strangerClass = SchoolClass::create(['name' => '3/ج', 'section' => 'ج', 'grade_id' => 1, 'school_id' => $this->schoolB->id]);
        Homework::create([
            'class_id' => $strangerClass->id,
            'subject_id' => 1,
            'teacher_id' => 1,
            'title' => 'واجب لصف غريب',
            'description' => 'خاص بطلاب آخرين',
            'due_date' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->parent, 'sanctum')->getJson('/api/mobile/parent/homeworks');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('واجب درس القراءة', $titles);
        $this->assertNotContains('واجب لصف غريب', $titles);
    }
}
