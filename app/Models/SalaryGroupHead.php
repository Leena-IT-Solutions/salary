<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGroupHead extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_group_id',
        'salary_master_id',
        'monthly_amount',
        'yearly_amount'
    ];
}
