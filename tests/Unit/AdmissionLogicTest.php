<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Admission;
use App\Models\Grade;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdmissionLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_request_creation_and_defaults()
    {
        $school = School::create(['name' => 'مدرسة القبول']);
        $grade = Grade::create(['name' => 'الصف الأول الابتدائي', 'school_id' => $school->id]);

        $admission = Admission::create([
            'parent_name' => 'محمد أحمد',
            'parent_email' => 'parent.test@example.com',
            'parent_phone' => '0501234567',
            'student_name' => 'علي محمد',
            'student_dob' => '2019-05-15',
            'grade_id' => $grade->id,
            'status' => 'pending',
            'notes' => 'طالب منقول',
        ]);

        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'student_name' => 'علي محمد',
            'status' => 'pending',
        ]);

        $this->assertEquals($grade->id, $admission->grade->id);
    }

    public function test_admission_status_transitions()
    {
        $school = School::create(['name' => 'مدرسة القبول']);
        $grade = Grade::create(['name' => 'الصف الثاني الابتدائي', 'school_id' => $school->id]);

        $admission = Admission::create([
            'parent_name' => 'سالم الحربي',
            'parent_email' => 'salem@example.com',
            'parent_phone' => '0555555555',
            'student_name' => 'خالد سالم',
            'student_dob' => '2018-02-10',
            'grade_id' => $grade->id,
            'status' => 'pending',
        ]);

        $admission->update(['status' => 'approved']);
        $this->assertEquals('approved', $admission->fresh()->status);

        $admission->update(['status' => 'rejected']);
        $this->assertEquals('rejected', $admission->fresh()->status);
    }
}
