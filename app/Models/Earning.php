<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    protected $fillable = [
        'earning_type_id',
        'name',
        'name_in_payslip',
        'calculation',
        'pay_time',
        'value',
        'is_active',
        'is_taxable',
        'is_pro_rata',
        'is_compensable',
        'is_ctc',
        'is_basic_pay',
        'is_gross_pay',
        'is_in_payslip',
    ];

    public function salary_groups(){
        return $this->morphToMany(SalaryGroup::class, 'salary_groupable');
    }

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }

    protected $appends = ['monthly'];

    public function getMonthlyAttribute(){
        return 0;
    }
}
