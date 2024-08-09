<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_group_id',
        'effective_from',
        'note',
        'ctc',
        'employer_contribution',
        'gross',
        'basic_pay',
        'remaining_amount',
        'earnings_total',
        'total_gross_percentage',
    ];
}
