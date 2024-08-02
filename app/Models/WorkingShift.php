<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'in',
        'out',
        'halfday',
        'is_next_day_out',
    ];
}
