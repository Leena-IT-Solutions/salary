<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEmployeeBreakup extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_employee_id',
        'name_in_payslip',
        'standard_amount',
        'actual_payable_amount',
        'employer_contribution_amount',
        'breakupable_id',
        'breakupable_type',
    ];

    public function breakupable() {
        return $this->morphTo();
    }
}
