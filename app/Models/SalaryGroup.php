<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'monthly_gross_salary',
        'anual_salary',
        'cost_to_company'
    ];
}
