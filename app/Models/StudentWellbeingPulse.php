<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentWellbeingPulse extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'mood',
        'emoji',
        'note',
        'week_date',
    ];

    protected $casts = [
        'week_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
