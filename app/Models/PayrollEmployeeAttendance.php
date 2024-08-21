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
        'ot_hours',
    ];

}
