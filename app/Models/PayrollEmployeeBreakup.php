<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEmployeeBreakup extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_employee_id',
        'amountable_id',
        'amountable_type',
        'name_in_payslip',
        'standard_amount',
        'actual_payable_amount',
        'employer_contribution_amount',
    ];
}
