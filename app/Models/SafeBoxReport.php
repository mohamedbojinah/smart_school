<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafeBoxReport extends Model
{
    use HasFactory;

    protected $table = 'safebox_reports';

    protected $fillable = [
        'school_id',
        'student_id',
        'token',
        'category',
        'title',
        'description',
        'urgency',
        'status',
        'counselor_response',
        'counselor_id',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
