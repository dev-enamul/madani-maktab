<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDesignation extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'employee_id',
        'designation_id',
        'start_date',
        'end_date',
    ];
}
