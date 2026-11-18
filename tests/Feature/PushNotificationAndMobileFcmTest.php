<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\StudentProfile;
use App\Models\DeviceToken;
use App\Models\AppNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\DB;

class PushNotificationAndMobileFcmTest extends TestCase
{
    use RefreshDatabase;

    protected User $parent;
    protected User $student;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'المدرسة الذكية الدولية',
            'academic_year' => '2025/2026',
        ]);

        $this->parent = User::factory()->create([
            'name' => 'ولي الأمر فهد',
            'email' => 'fahad.parent@test.com',
            'role' => 'parent',
            'school_id' => $this->school->id,
        ]);
        $this->parent->assignRole('parent');

        $this->student = User::factory()->create([
            'name' => 'الطالب مشعل فهد',
            'role' => 'student',
            'school_id' => $this->school->id,
        ]);
        $this->student->assignRole('student');

        $grade = Grade::create(['name' => 'المرحلة المتوسطة', 'school_id' => $this->school->id]);
        $class = SchoolClass::create(['name' => '1/أ', 'section' => 'أ', 'grade_id' => $grade->id, 'school_id' => $this->school->id]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'class_id' => $class->id,
            'wallet_balance' => 50,
        ]);

        DB::table('parent_student')->insert([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 1: تسجيل رمز جهاز FCM للمستخدم
     */
    public function test_user_can_register_fcm_device_token()
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/mobile/fcm-token', [
            'fcm_token' => 'fcm-sample-token-abc-12345',
            'device_type' => 'android',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'تم تسجيل الجهاز بنجاح لاستقبال الإشعارات',
        ]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->parent->id,
            'fcm_token' => 'fcm-sample-token-abc-12345',
            'device_type' => 'android',
            'is_active' => true,
        ]);
    }

    /**
     * Test 2: إعادة إرسال نفس التوكن تحدث السجل القائم دون تكرار
     */
    public function test_registering_same_device_token_is_idempotent()
    {
        $this->actingAs($this->parent, 'sanctum')->postJson('/api/mobile/fcm-token', [
            'fcm_token' => 'token-unique-999',
            'device_type' => 'android',
        ]);

        $this->actingAs($this->parent, 'sanctum')->postJson('/api/mobile/fcm-token', [
            'fcm_token' => 'token-unique-999',
            'device_type' => 'android',
        ]);

        $this->assertEquals(1, DeviceToken::where('user_id', $this->parent->id)->where('fcm_token', 'token-unique-999')->count());
    }

    /**
     * Test 3: إرسال واسترجاع الإشعارات عبر API مع عداد غير المقروء
     */
    public function test_parent_receives_and_fetches_notifications()
    {
        PushNotificationService::sendToUser(
            $this->parent,
            '📊 رصد درجات جديدة',
            'تم رصد درجة اختبار الرياضيات للطالب مشعل (20/20)',
            ['subject' => 'math', 'score' => 20],
            'grade',
            $this->school->id,
            $this->student->id
        );

        $response = $this->actingAs($this->parent, 'sanctum')->getJson('/api/mobile/notifications');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'unread_count' => 1,
            ]
        ]);

        $this->assertEquals('📊 رصد درجات جديدة', $response->json('data.notifications.0.title'));
    }

    /**
     * Test 4: تحديد الإشعار كمقروء
     */
    public function test_mark_notification_as_read()
    {
        $notif = PushNotificationService::sendToUser(
            $this->parent,
            'تنبيه الحضور',
            'تم تسجيل الحضور اليوم',
            [],
            'attendance'
        );

        $this->assertFalse($notif->fresh()->is_read);

        $response = $this->actingAs($this->parent, 'sanctum')->postJson("/api/mobile/notifications/{$notif->id}/read");
        $response->assertStatus(200);

        $this->assertTrue($notif->fresh()->is_read);
    }

    /**
     * Test 5: زر الإشعار التجريبي يرسل إشعاراً بنجاح
     */
    public function test_send_test_notification_endpoint()
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/mobile/notifications/test');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'تم إرسال الإشعار التجريبي بنجاح!',
        ]);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->parent->id,
            'title' => '🔔 إشعار تجريبي من Smart School',
        ]);
    }

    /**
     * Test 6: مسح QR حضور الطالب يرسل إشعاراً فورياً لهاتف ولي الأمر
     */
    public function test_scanning_student_attendance_qr_triggers_parent_push_notification()
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $this->school->id,
        ]);
        $teacher->assignRole('teacher');

        $response = $this->actingAs($teacher)->postJson('/attendance/scan-qr', [
            'qr_code' => "STD-{$this->student->id}",
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // التحقق من وصول الإشعار لولي الأمر
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->parent->id,
            'type' => 'attendance',
            'student_id' => $this->student->id,
        ]);
    }
}
