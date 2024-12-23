<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewSchedule extends Model
{
    use HasFactory; 

    protected $fillable = [
        'candidate_id',
        'interviewer_id',
        'requested_at',
        'attended_at',
        'location',
        'status',
        'meeting_link',
        'notes',
    ];
    
}
