<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\TeacherEvaluation;
use App\Models\OfficialLetter;
use App\Models\SchoolAnnouncement;
use App\Models\ManagerApproval;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManagerExecutiveFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $teacher;
    protected User $student;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'teacher']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'parent']);

        $this->school = School::create([
            'id' => 1,
            'name' => 'المدرسة الذكية التجريبية',
            'academic_year' => '2026/2027',
        ]);

        $this->manager = User::create([
            'name' => 'المدير العام',
            'email' => 'manager@school.com',
            'password' => bcrypt('12345678'),
            'role' => 'manager',
            'school_id' => 1,
        ]);
        $this->manager->assignRole('manager');

        $this->teacher = User::create([
            'name' => 'أحمد المعلم',
            'email' => 'teacher1@school.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'school_id' => 1,
        ]);
        $this->teacher->assignRole('teacher');

        $this->student = User::create([
            'name' => 'علي الطالب',
            'email' => 'student1@school.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'school_id' => 1,
        ]);
        $this->student->assignRole('student');
    }

    /** @test */
    public function manager_can_access_executive_dashboard()
    {
        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('لوحة القيادة التنفيذية');
        $response->assertSee('إجمالي الطلاب');
        $response->assertSee('كادر المعلمين');
        $response->assertSee('حضور اليوم');
        $response->assertViewHas('stats');
        $response->assertViewHas('attendanceDaysLabels');
        $response->assertViewHas('gradeLabels');
    }

    /** @test */
    public function manager_can_access_ai_executive_advisor()
    {
        $response = $this->actingAs($this->manager)->get(route('manager.ai_advisor'));
        $response->assertStatus(200);
        $response->assertSee('المستشار الذكي لمدير المدرسة');
        $response->assertSee('مؤشر الكفاءة المدرسية العام');
        $response->assertSee('رادار الإنذار المبكر');
        $response->assertViewHas('executiveSummary');
        $response->assertViewHas('atRiskStudents');
    }

    /** @test */
    public function manager_can_view_and_store_teacher_evaluation()
    {
        $indexResponse = $this->actingAs($this->manager)->get(route('manager.evaluations.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('منظومة تقييم أداء المعلمين');
        $indexResponse->assertSee($this->teacher->name);

        $storeResponse = $this->actingAs($this->manager)->post(route('manager.evaluations.store', $this->teacher->id), [
            'punctuality_rating' => 5,
            'teaching_quality_rating' => 4,
            'cooperation_rating' => 5,
            'recommendation' => 'صرف مكافأة تميز',
            'notes' => 'معلم مبدع ومنضبط',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('teacher_evaluations', [
            'teacher_id' => $this->teacher->id,
            'punctuality_rating' => 5,
            'teaching_quality_rating' => 4,
            'recommendation' => 'صرف مكافأة تميز',
        ]);
    }

    /** @test */
    public function manager_can_issue_and_print_official_letters()
    {
        $createResponse = $this->actingAs($this->manager)->get(route('manager.letters.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('تحرير خطاب رسمي');

        $storeResponse = $this->actingAs($this->manager)->post(route('manager.letters.store'), [
            'recipient_id' => $this->teacher->id,
            'type' => 'appreciation',
            'title' => 'شكر وتقدير على الإخلاص في التدريس',
            'content' => 'نتقدم لكم بخالص الشكر والتقدير لجهودكم الملموسة.',
        ]);

        $letter = OfficialLetter::where('recipient_id', $this->teacher->id)->first();
        $this->assertNotNull($letter);
        $this->assertEquals('appreciation', $letter->type);

        $storeResponse->assertRedirect(route('manager.letters.print', $letter->id));

        $printResponse = $this->actingAs($this->manager)->get(route('manager.letters.print', $letter->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('شــهـادة شـكـر وتـقـديـر');
        $printResponse->assertSee($this->teacher->name);
        $printResponse->assertSee($letter->reference_no);
    }

    /** @test */
    public function teacher_can_view_received_official_letters()
    {
        OfficialLetter::create([
            'school_id' => 1,
            'sender_id' => $this->manager->id,
            'recipient_id' => $this->teacher->id,
            'type' => 'appreciation',
            'reference_no' => 'SM-2026-TEST01',
            'title' => 'شهادة تكريم سنوية',
            'content' => 'مبارك تكريمكم لهذا العام.',
        ]);

        $response = $this->actingAs($this->teacher)->get(route('teacher.letters.index'));
        $response->assertStatus(200);
        $response->assertSee('الخطابات وشهادات الشكر الموجهة لك');
        $response->assertSee('شهادة تكريم سنوية');
    }

    /** @test */
    public function manager_can_manage_approvals_center()
    {
        $indexResponse = $this->actingAs($this->manager)->get(route('manager.approvals.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('مركز الاعتمادات والقرارات الإدارية');

        $storeResponse = $this->actingAs($this->manager)->post(route('manager.approvals.store'), [
            'type' => 'publish_grades',
            'title' => 'اعتماد إعلان نتائج الفصل الأول',
            'description' => 'المصادقة على العلامات والشهادات',
        ]);
        $storeResponse->assertRedirect();

        $approval = ManagerApproval::where('type', 'publish_grades')->first();
        $this->assertNotNull($approval);
        $this->assertEquals('pending', $approval->status);

        $updateResponse = $this->actingAs($this->manager)->post(route('manager.approvals.update', $approval->id), [
            'status' => 'approved',
            'notes' => 'معتمد رسمياً وتم إقفال الرصد',
        ]);
        $updateResponse->assertRedirect();

        $this->assertDatabaseHas('manager_approvals', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function manager_can_publish_announcement_and_emergency_broadcast()
    {
        $indexResponse = $this->actingAs($this->manager)->get(route('manager.announcements.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('مركز التعاميم المدرسية وبلاغات الطوارئ');

        $storeResponse = $this->actingAs($this->manager)->post(route('manager.announcements.store'), [
            'title' => 'تعليق الدراسة بسبب الطقس',
            'content' => 'تقرر تعليق الدوام المدرسي حفاظاً على سلامة الطلبة.',
            'target_role' => 'all',
            'priority' => 'emergency',
        ]);
        $storeResponse->assertRedirect();

        $this->assertDatabaseHas('school_announcements', [
            'title' => 'تعليق الدراسة بسبب الطقس',
            'priority' => 'emergency',
            'is_active' => true,
        ]);

        // Emergency banner should appear in dashboard
        $dashResponse = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $dashResponse->assertSee('🚨 نداء طوارئ صادر من الإدارة: تعليق الدراسة بسبب الطقس');
    }

    /** @test */
    public function manager_can_view_comprehensive_annual_report()
    {
        $response = $this->actingAs($this->manager)->get(route('manager.reports.comprehensive'));
        $response->assertStatus(200);
        $response->assertSee('تقرير الأداء المؤسسي السنوي والتحليلي الشامل');
        $response->assertSee('الموقف المالي وحركة الرسوم والمصروفات');
        $response->assertSee('خلاصة تقييم الكادر التعليمي');
        $response->assertSee('الختم الرسمي للإدارة');
    }

    /** @test */
    public function non_manager_cannot_access_manager_portal()
    {
        $response = $this->actingAs($this->student)->get(route('manager.dashboard'));
        $response->assertStatus(403);

        $teacherResponse = $this->actingAs($this->teacher)->get(route('manager.dashboard'));
        $teacherResponse->assertStatus(403);
    }
}
