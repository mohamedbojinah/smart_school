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
        // 1. تقييم أداء المعلمين والكادر
        Schema::create('teacher_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('manager_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('academic_year')->nullable()->default('2026/2027');
            $table->unsignedTinyInteger('punctuality_rating')->default(5); // الانضباط والمواظبة (1-5)
            $table->unsignedTinyInteger('teaching_quality_rating')->default(5); // جودة التدريس والشرح (1-5)
            $table->unsignedTinyInteger('cooperation_rating')->default(5); // التعاون والالتزام باللوائح (1-5)
            $table->decimal('overall_rating', 3, 2)->default(5.00); // التقييم الإجمالي
            $table->string('recommendation')->nullable(); // التوصية (ترقية، شكر، تدريب)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. الخطابات الرسمية الموجهة (شهادات شكر، لفت نظر، توجيه)
        Schema::create('official_letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('sender_id'); // المدير
            $table->unsignedBigInteger('recipient_id'); // المعلم أو الإداري
            $table->enum('type', ['appreciation', 'warning', 'guidance'])->default('appreciation');
            $table->string('reference_no')->unique(); // رقم القيد / الصادر الرسمي
            $table->string('title');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 3. التعاميم والبلاغات المدرسية وتنبيهات الطوارئ
        Schema::create('school_announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('user_id'); // الناشر
            $table->string('title');
            $table->text('content');
            $table->enum('target_role', ['all', 'teacher', 'student', 'parent', 'admin'])->default('all');
            $table->enum('priority', ['normal', 'high', 'emergency'])->default('normal');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 4. مركز الاعتمادات والقرارات الإدارية والتنفيذية
        Schema::create('manager_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('type'); // publish_grades, promote_students, major_expense, academic_plan
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('action_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_approvals');
        Schema::dropIfExists('school_announcements');
        Schema::dropIfExists('official_letters');
        Schema::dropIfExists('teacher_evaluations');
    }
};
