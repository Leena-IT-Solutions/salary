<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'in',
        'out',
        'is_next_day_out',
    ];
}
