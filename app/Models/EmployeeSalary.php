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
        'checking_gross_pay',
        'gross_pay',
        'basic_pay',
        'net_pay',
        'employer_contribution',
        'remaining_amount',
        'earnings_total',
        'total_gross_percentage',
        'per_hour',
        'per_minute',
    ];

    public function salary_group(){
        return $this->belongsTo(SalaryGroup::class);
    }

    public function es_statutories(){
        return $this->hasMany(ESStatutory::class);
    }
}
