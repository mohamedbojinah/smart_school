<?php

namespace App\Services;

use App\Models\User;
use App\Models\DeviceToken;
use App\Models\AppNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * إرسال إشعار فوري لمستخدم محدد (ولي أمر / طالب / معلم)
     * يتم إرساله عبر Firebase Cloud Messaging وحفظه في سجل الإشعارات
     *
     * @param User|int $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string $type ('attendance', 'grade', 'wallet', 'leave', 'homework', 'announcement', 'general')
     * @param int|null $schoolId
     * @param int|null $studentId
     * @return AppNotification
     */
    public static function sendToUser($user, string $title, string $body, array $data = [], string $type = 'general', ?int $schoolId = null, ?int $studentId = null): AppNotification
    {
        $userId = $user instanceof User ? $user->id : (int)$user;

        // 1. تسجيل الإشعار في قاعدة البيانات
        $notification = AppNotification::create([
            'user_id' => $userId,
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);

        // 2. جلب رموز الأجهزة النشطة للمستخدم
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (!empty($tokens)) {
            self::sendFcmNotification($tokens, $title, $body, array_merge($data, [
                'notification_id' => (string)$notification->id,
                'type' => $type,
                'school_id' => (string)($schoolId ?? ''),
                'student_id' => (string)($studentId ?? ''),
            ]));
        }

        return $notification;
    }

    /**
     * إرسال إشعار لمجموعة من المستخدمين (مثلاً: جميع أولياء أمور مدرسة معينة)
     */
    public static function sendToMultipleUsers(array $userIds, string $title, string $body, array $data = [], string $type = 'general', ?int $schoolId = null)
    {
        $created = [];
        foreach ($userIds as $userId) {
            $created[] = self::sendToUser($userId, $title, $body, $data, $type, $schoolId);
        }
        return $created;
    }

    /**
     * إرسال حزمة الإشعار إلى خوادم Firebase Cloud Messaging
     * يدعم إيقاظ التطبيق حتى لو كان مغلقاً تماماً في الخلفية (High Priority)
     */
    protected static function sendFcmNotification(array $tokens, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('services.fcm.key') ?? env('FCM_SERVER_KEY');

        // إذا لم يكن مفتاح FCM متوفراً في البيئة التجريبية، نسجل ذلك في السجل بدون تعطل
        if (empty($serverKey)) {
            Log::info("FCM push queued (Simulated, FCM_SERVER_KEY not set): '{$title}' to " . count($tokens) . " tokens.");
            return true;
        }

        try {
            // إرسال عبر Firebase Legacy HTTP API (مضمون ومتوافق ومباشر لجميع إصدارات أندرويد وiOS)
            $payload = [
                'registration_ids' => $tokens,
                'priority' => 'high', // ضروري لإظهار الإشعار حتى أثناء قفل الشاشة أو إغلاق التطبيق
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1,
                    'channel_id' => 'smart_school_notifications',
                    'android_channel_id' => 'smart_school_notifications',
                    'icon' => 'ic_notification',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'title' => $title,
                    'body' => $body,
                ]),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'smart_school_notifications',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(5)->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                Log::info("FCM Notification sent successfully to " . count($tokens) . " devices.");
                return true;
            } else {
                Log::warning("FCM Notification returned status: " . $response->status() . " Body: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("FCM Push Exception: " . $e->getMessage());
            return false;
        }
    }
}
