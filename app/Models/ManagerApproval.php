<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'manager_id',
        'type',
        'title',
        'description',
        'status',
        'notes',
        'action_date',
    ];

    protected $casts = [
        'action_date' => 'datetime',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
