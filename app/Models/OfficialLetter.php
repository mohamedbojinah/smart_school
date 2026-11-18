<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'sender_id',
        'recipient_id',
        'type',
        'reference_no',
        'title',
        'content',
        'is_read',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
