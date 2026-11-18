<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'slug',
        'headline',
        'about_me',
        'skills',
        'volunteer_hours',
        'is_public',
        'verified_by_school',
        'verification_seal_number',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'verified_by_school' => 'boolean',
        'volunteer_hours' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function items()
    {
        return $this->hasMany(StudentPortfolioItem::class, 'student_portfolio_id');
    }

    public function getSkillsArrayAttribute(): array
    {
        if (empty($this->skills)) {
            return [];
        }
        if (is_array($this->skills)) {
            return $this->skills;
        }
        $decoded = json_decode($this->skills, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_filter(array_map('trim', explode(',', $this->skills)));
    }
}
