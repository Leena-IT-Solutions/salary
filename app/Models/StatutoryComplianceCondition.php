<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutoryComplianceCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'statutory_compliance_id',
        'gender',
        'state',
        'salary_type',
        'calculation',
        'min_salary',
        'max_salary',
        'restrict_salary_for_calculation',
        'employee_contribution',
        'max_employee_contribution',
        'employer_contribution',
        'max_employer_contribution',
        'is_active',
    ];

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }
}
