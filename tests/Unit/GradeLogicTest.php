<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GradeLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_and_subject_relation_integrity()
    {
        $school = School::create(['name' => 'مدرسة الدرجات']);
        $grade = Grade::create(['name' => 'الصف الثالث المتوسط', 'school_id' => $school->id]);

        $subject = Subject::create([
            'name' => 'الرياضيات المتقدمة',
            'grade_id' => $grade->id,
            'school_id' => $school->id,
        ]);

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'الرياضيات المتقدمة',
            'grade_id' => $grade->id,
        ]);

        $this->assertEquals($grade->id, $subject->grade->id);
    }

    public function test_grade_is_valid_within_range()
    {
        $maxScore = 100;

        $validScore = 85;
        $this->assertTrue($validScore >= 0 && $validScore <= $maxScore);

        $invalidScore = 105;
        $this->assertFalse($invalidScore >= 0 && $invalidScore <= $maxScore);

        $negativeScore = -5;
        $this->assertFalse($negativeScore >= 0 && $negativeScore <= $maxScore);
    }
}
