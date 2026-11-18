<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\SafeBoxReport;
use App\Models\StudentWellbeingPulse;
use App\Models\StudentPortfolio;
use App\Models\StudentPortfolioItem;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class SafeBoxAndStudentPortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $admin;
    protected User $student;
    protected User $parent;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'admin']);
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

        $this->admin = User::create([
            'name' => 'مسؤول الإرشاد',
            'email' => 'admin@school.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
            'school_id' => 1,
        ]);
        $this->admin->assignRole('admin');

        $this->student = User::create([
            'name' => 'سامي المتفوق',
            'email' => 'student@school.com',
            'password' => bcrypt('12345678'),
            'role' => 'student',
            'school_id' => 1,
        ]);
        $this->student->assignRole('student');

        $this->parent = User::create([
            'name' => 'أبو سامي',
            'email' => 'parent@school.com',
            'password' => bcrypt('12345678'),
            'role' => 'parent',
            'school_id' => 1,
        ]);
        $this->parent->assignRole('parent');

        // Link parent to student
        DB::table('parent_student')->insert([
            'parent_id'  => $this->parent->id,
            'student_id' => $this->student->id,
        ]);
    }

    // ==========================================
    // اختبارات صندوق الأمان ومكافحة التنمر (SafeBox)
    // ==========================================

    /** @test */
    public function student_can_view_safebox_page()
    {
        $response = $this->actingAs($this->student)->get(route('student.safebox.index'));
        $response->assertStatus(200);
        $response->assertSee('صندوق الأمان المدرسي ومكافحة التنمر');
        $response->assertSee('كيف تشعر هذا الأسبوع؟');
    }

    /** @test */
    public function student_can_submit_anonymous_safebox_report()
    {
        $response = $this->actingAs($this->student)->post(route('student.safebox.report'), [
            'category'     => 'bullying',
            'title'        => 'مضايقات في فناء المدرسة',
            'description'  => 'هناك طلاب يتعمدون أخذ كتبي وسخريتي في الفسحة.',
            'urgency'      => 'critical',
            'is_anonymous' => 1,
        ]);

        $response->assertRedirect();
        
        $report = SafeBoxReport::first();
        $this->assertNotNull($report);
        $this->assertNull($report->student_id); // Anonymous!
        $this->assertEquals('bullying', $report->category);
        $this->assertEquals('critical', $report->urgency);
        $this->assertStringStartsWith('SAFE-', $report->token);
    }

    /** @test */
    public function student_can_track_safebox_report_with_token()
    {
        $report = SafeBoxReport::create([
            'school_id'          => 1,
            'student_id'         => null,
            'token'              => 'SAFE-TEST01',
            'category'           => 'bullying',
            'title'              => 'بلاغ سري',
            'description'        => 'تفاصيل المشكلة',
            'urgency'            => 'high',
            'status'             => 'in_review',
            'counselor_response' => 'تم التواصل مع مشرف الدور وجاري حل المشكلة بحذر.',
        ]);

        $response = $this->actingAs($this->student)->get(route('student.safebox.index', ['token' => 'SAFE-TEST01']));
        $response->assertStatus(200);
        $response->assertSee('SAFE-TEST01');
        $response->assertSee('تم التواصل مع مشرف الدور وجاري حل المشكلة بحذر');
    }

    /** @test */
    public function student_can_record_weekly_mood_pulse()
    {
        $response = $this->actingAs($this->student)->post(route('student.safebox.pulse'), [
            'mood' => 'great',
            'note' => 'أشعر بالحماس الشديد بعد فوز فريقي في مسابقة العلوم!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_wellbeing_pulses', [
            'student_id' => $this->student->id,
            'mood'       => 'great',
            'emoji'      => '😃',
        ]);
    }

    /** @test */
    public function admin_or_manager_can_review_and_respond_to_safebox_report()
    {
        $report = SafeBoxReport::create([
            'school_id'   => 1,
            'student_id'  => null,
            'token'       => 'SAFE-BULLY1',
            'category'    => 'bullying',
            'title'       => 'تنمر لفظي',
            'description' => 'أحتاج مساعدة عاجلة',
            'urgency'     => 'critical',
            'status'      => 'pending',
        ]);

        // Manager views list
        $viewResponse = $this->actingAs($this->manager)->get(route('manager.safebox.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('SAFE-BULLY1');

        // Admin responds
        $respondResponse = $this->actingAs($this->admin)->post(route('admin.safebox.respond', $report->id), [
            'counselor_response' => 'لا تقلق أبداً، تم اتخاذ الإجراء اللازم وضمان حمايتك التامة.',
            'status'             => 'resolved',
        ]);

        $respondResponse->assertRedirect();
        $this->assertDatabaseHas('safebox_reports', [
            'id'                 => $report->id,
            'status'             => 'resolved',
            'counselor_response' => 'لا تقلق أبداً، تم اتخاذ الإجراء اللازم وضمان حمايتك التامة.',
        ]);
    }

    // ==========================================
    // اختبارات سجل الإنجاز الرقمي الموثق (Portfolio)
    // ==========================================

    /** @test */
    public function student_can_manage_portfolio_and_add_items()
    {
        $response = $this->actingAs($this->student)->get(route('student.portfolio.index'));
        $response->assertStatus(200);
        $response->assertSee('سجل الإنجاز الرقمي الموثق للطالب');

        // Update profile
        $updateResponse = $this->actingAs($this->student)->post(route('student.portfolio.profile.update'), [
            'headline'        => 'مبرمج مستقبلي وباحث ناشئ',
            'about_me'        => 'شغوف بعلوم الحاسب والذكاء الاصطناعي',
            'skills'          => 'Python, حل المشكلات, الابتكار',
            'volunteer_hours' => 25,
            'is_public'       => 1,
        ]);
        $updateResponse->assertRedirect();

        $portfolio = StudentPortfolio::where('user_id', $this->student->id)->first();
        $this->assertNotNull($portfolio);
        $this->assertEquals(25, $portfolio->volunteer_hours);

        // Add item
        $itemResponse = $this->actingAs($this->student)->post(route('student.portfolio.items.store'), [
            'title'       => 'مشروع حساس الري الذكي',
            'category'    => 'project',
            'description' => 'جهاز أردوينو لقياس رطوبة التربة آلياً.',
            'project_url' => 'https://github.com/student/smart-irrigation',
            'year'        => '2026',
        ]);
        $itemResponse->assertRedirect();

        $this->assertDatabaseHas('student_portfolio_items', [
            'student_portfolio_id' => $portfolio->id,
            'title'                => 'مشروع حساس الري الذكي',
            'category'             => 'project',
        ]);
    }

    /** @test */
    public function anyone_can_view_verified_public_portfolio()
    {
        $portfolio = StudentPortfolio::create([
            'user_id'                  => $this->student->id,
            'school_id'                => 1,
            'slug'                     => 'sami-expert-2026',
            'headline'                 => 'مخترع واعد',
            'about_me'                 => 'نبذة عن طموحاتي وتفوقي',
            'skills'                   => 'الروبوتات, القيادة, الإلقاء',
            'volunteer_hours'          => 40,
            'is_public'                => true,
            'verified_by_school'       => true,
            'verification_seal_number' => 'PORT-2026-SEAL12',
        ]);

        StudentPortfolioItem::create([
            'student_portfolio_id' => $portfolio->id,
            'title'                => 'المركز الأول في معرض العلوم',
            'category'             => 'competition',
            'description'          => 'تكريم من وزارة التعليم للابتكار.',
            'year'                 => '2026',
        ]);

        // Public access without logging in
        $response = $this->get(route('portfolio.public', 'sami-expert-2026'));
        $response->assertStatus(200);
        $response->assertSee('سامي المتفوق');
        $response->assertSee('مخترع واعد');
        $response->assertSee('سجل إنجاز رقمي معتمد رسمياً');
        $response->assertSee('المركز الأول في معرض العلوم');
        $response->assertSee('PORT-2026-SEAL12');
        $response->assertSee('ساعات الخدمة المجتمعية والتطوع');
    }

    /** @test */
    public function parent_can_view_child_portfolio()
    {
        $portfolio = StudentPortfolio::create([
            'user_id'                  => $this->student->id,
            'school_id'                => 1,
            'slug'                     => 'child-sami-slug',
            'headline'                 => 'طالب متميز',
            'is_public'                => true,
            'verification_seal_number' => 'PORT-2026-CHILD1',
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.child.portfolio', $this->student->id));
        $response->assertRedirect(route('portfolio.public', 'child-sami-slug'));
    }
}
