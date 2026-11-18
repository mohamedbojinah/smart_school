<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. جدول رموز الأجهزة المسجلة للإشعارات الفورية (FCM Device Tokens)
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fcm_token', 500);
            $table->string('device_type')->default('android'); // android, ios, web
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->unique(['user_id', 'fcm_token']);
        });

        // 2. سجل الإشعارات المرسلة للمستخدم والتطبيق (In-App & Push Notifications Log)
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('general'); // attendance, grade, wallet, leave, homework, announcement
            $table->json('data')->nullable(); // Additional payloads like route, entity_id
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['school_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('device_tokens');
    }
};
