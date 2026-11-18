<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SecurityAndMultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    protected $school1;
    protected $school2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school1 = School::create([
            'id' => 1,
            'name' => 'المدرسة الذكية الأولى',
            'academic_year' => '2026/2027',
        ]);

        $this->school2 = School::create([
            'id' => 2,
            'name' => 'المدرسة الذكية الثانية',
            'academic_year' => '2026/2027',
        ]);
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/manager/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/teacher/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/student/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/parent/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_student_cannot_access_manager_or_admin_routes()
    {
        $student = User::factory()->create([
            'school_id' => $this->school1->id,
            'role' => 'student',
        ]);
        $student->assignRole('student');

        $response = $this->actingAs($student)->get('/admin/dashboard');
        $this->assertTrue(in_array($response->status(), [302, 403]));

        $response = $this->actingAs($student)->get('/manager/dashboard');
        $this->assertTrue(in_array($response->status(), [302, 403]));

        $response = $this->actingAs($student)->get('/manager/system-logs');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_teacher_cannot_grade_unassigned_class()
    {
        $teacher = User::factory()->create([
            'school_id' => $this->school1->id,
            'role' => 'teacher',
        ]);
        $teacher->assignRole('teacher');

        $grade = Grade::create(['name' => 'الصف الأول', 'school_id' => $this->school1->id]);
        $class = SchoolClass::create(['name' => '1/أ', 'section' => 'أ', 'grade_id' => $grade->id, 'school_id' => $this->school1->id]);
        $subject = Subject::create(['name' => 'الرياضيات', 'grade_id' => $grade->id, 'school_id' => $this->school1->id]);

        // Attempting to access create grades without an entry in teacher_subject_section should return 403
        $response = $this->actingAs($teacher)->get("/teacher/teacher/grades/{$subject->id}/{$class->id}");
        $response->assertStatus(403);
    }

    public function test_parent_cannot_transfer_funds_to_unrelated_student()
    {
        $parent = User::factory()->create([
            'school_id' => $this->school1->id,
            'role' => 'parent',
        ]);
        $parent->assignRole('parent');

        // Legitimate child
        $childUser = User::factory()->create(['school_id' => $this->school1->id, 'role' => 'student']);
        $childProfile = StudentProfile::create(['user_id' => $childUser->id, 'wallet_balance' => 100]);
        $parent->children()->attach($childUser->id);

        // Stranger child
        $strangerUser = User::factory()->create(['school_id' => $this->school1->id, 'role' => 'student']);
        $strangerProfile = StudentProfile::create(['user_id' => $strangerUser->id, 'wallet_balance' => 50]);

        $response = $this->actingAs($parent)->post(route('parent.wallet.transfer'), [
            'from_student_id' => $childProfile->id,
            'to_student_id' => $strangerProfile->id,
            'amount' => 20,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(100, $childProfile->fresh()->wallet_balance);
        $this->assertEquals(50, $strangerProfile->fresh()->wallet_balance);
    }

    public function test_wallet_purchase_fails_when_funds_are_insufficient()
    {
        $admin = User::factory()->create([
            'school_id' => $this->school1->id,
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $student = User::factory()->create(['school_id' => $this->school1->id, 'role' => 'student']);
        $profile = StudentProfile::create(['user_id' => $student->id, 'wallet_balance' => 15]);

        $response = $this->actingAs($admin)->post('/admin/wallet/purchase', [
            'student_id' => $profile->id,
            'amount' => 50,
            'item' => 'وجبة ومشروب من المقصف',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(15, $profile->fresh()->wallet_balance);
    }
}
