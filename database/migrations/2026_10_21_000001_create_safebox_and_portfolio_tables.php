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
        // ==========================================
        // 1. صندوق الأمان المدرسي ومكافحة التنمر (SafeBox)
        // ==========================================
        Schema::create('safebox_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('student_id')->nullable(); // مجهول إن كان خالي
            $table->string('token')->unique(); // رمز سري للمتابعة بدون كشف الهوية
            $table->enum('category', [
                'bullying',          // تنمر واعتداء
                'mental_stress',     // قلق وضغط نفسي
                'family_issue',      // مشاكل أسرية وخاصة
                'academic_pressure', // تعثر وضغط دراسي حرج
                'safety_hazard',     // خطر أمني أو بيئي
                'other'              // أخرى
            ])->default('bullying');
            $table->string('title');
            $table->text('description');
            $table->enum('urgency', ['normal', 'high', 'critical'])->default('normal');
            $table->enum('status', ['pending', 'in_review', 'resolved'])->default('pending');
            $table->text('counselor_response')->nullable();
            $table->unsignedBigInteger('counselor_id')->nullable(); // المعالج / المرشد
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('counselor_id')->references('id')->on('users')->onDelete('set null');
        });

        // 2. استبيان نبض المشاعر والرفاهية النفسية الأسبوعي
        Schema::create('student_wellbeing_pulses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('student_id');
            $table->enum('mood', ['great', 'calm', 'anxious', 'sad', 'stressed'])->default('calm');
            $table->string('emoji', 16)->default('😌');
            $table->text('note')->nullable();
            $table->date('week_date');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ==========================================
        // 2. سجل الإنجاز الرقمي الموثق للطالب (Student Portfolio)
        // ==========================================
        Schema::create('student_portfolios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // الطالب
            $table->unsignedBigInteger('school_id')->default(1);
            $table->string('slug')->unique(); // الرابط العام للمشاركة
            $table->string('headline')->nullable(); // نبذة سريعة مثل "مبتكر واعد وشغوف بالعلوم"
            $table->text('about_me')->nullable();
            $table->text('skills')->nullable(); // JSON أو نص المهارات
            $table->unsignedInteger('volunteer_hours')->default(0); // ساعات التطوع
            $table->boolean('is_public')->default(true);
            $table->boolean('verified_by_school')->default(true); // اعتماد المدرسة الرسمي
            $table->string('verification_seal_number')->unique(); // رقم الختم المعتمد
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // عناصر ومشاريع البورتفوليو
        Schema::create('student_portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_portfolio_id');
            $table->string('title');
            $table->enum('category', [
                'project',      // مشروع مدرسي أو ابتكار
                'certificate',  // شهادة أو تكريم
                'competition',  // مسابقة أو أولمبياد
                'research',     // بحث علمي أو مقال
                'art_or_sport'  // موهبة فنية أو رياضية
            ])->default('project');
            $table->text('description')->nullable();
            $table->string('project_url')->nullable(); // رابط للمشروع إن وجد
            $table->string('year', 10)->nullable();
            $table->timestamps();

            $table->foreign('student_portfolio_id')->references('id')->on('student_portfolios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_portfolio_items');
        Schema::dropIfExists('student_portfolios');
        Schema::dropIfExists('student_wellbeing_pulses');
        Schema::dropIfExists('safebox_reports');
    }
};
