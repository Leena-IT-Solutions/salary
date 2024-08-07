<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGroupData extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_group_id',
        'common_id',
        'what',
    ];

}
