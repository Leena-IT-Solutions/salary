<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'working_shift_id',
        'dt',
    ];

    public function working_shift(){
        return $this->belongsTo(WorkingShift::class);
    }

    public function employee_attendance(){
        return $this->hasMany(EmployeeAttendance::class);
    }
}
