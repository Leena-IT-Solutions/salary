<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year_id',
        'payroll_name',
        'from',
        'to',
        'working_days',
        'actual_days',
        'ctc',
        'basic_pay',
        'gross_pay',
        'total_earning',
        'overtime_earning',
        'reimbursement',
        'loan_disbursal',
        'gross_salary',
        'gross_deduction',
        'net_payable_amount',
    ];

    public function payroll_employees(){
        return $this->hasMany(PayrollEmployee::class);
    }

    protected static function booted () {
        static::deleting(function(Payroll $payroll) {
            $employees = $payroll->payroll_employees()->get();
            foreach($employees as $employee){
                $employee->payroll_employee_attendances()->delete();
                $employee->payroll_employee_breakups()->delete();
            }
            $payroll->payroll_employees()->delete();
        });
    }
}
