<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'manager_id',
        'teacher_id',
        'academic_year',
        'punctuality_rating',
        'teaching_quality_rating',
        'cooperation_rating',
        'overall_rating',
        'recommendation',
        'notes',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
