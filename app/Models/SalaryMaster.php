<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'head',
        'side',
        'rule',
        'multiplier',
        'repeat'
    ];
}
