<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_portfolio_id',
        'title',
        'category',
        'description',
        'project_url',
        'year',
    ];

    public function portfolio()
    {
        return $this->belongsTo(StudentPortfolio::class, 'student_portfolio_id');
    }
}
