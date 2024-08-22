<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'services_type_id',
        'name',
        'name_in_payslip',
        'calculation',
        'pay_time',
        'value',
        'is_active',
        'is_pro_rata',
        'is_in_payslip',
        'is_compulsory',
    ];

    public function salary_groups(){
        return $this->morphToMany(SalaryGroup::class, 'salary_groupable');
    }

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }

    /* arbitary fields */
    protected $appends = ['monthly'];

    public function getMonthlyAttribute(){
        return 0;
    }
}
