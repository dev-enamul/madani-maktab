<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurmentUnit extends Model
{
    use HasFactory; 

     protected $fillable = [
        'name',
        'short_name',
    ];  
}
