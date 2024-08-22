<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_employee_id',
        'lop',
        'payable_days',
        'ot_hours',
        'ot_amount',
    ];

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }

}
