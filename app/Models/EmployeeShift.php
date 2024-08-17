<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\LeaveApproval;

class EmployeeShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'working_shift_id',
        'dt',
        'late',
        'early',
        'lop',
        'status',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function working_shift(){
        return $this->belongsTo(WorkingShift::class);
    }

    public function employee_attendance(){
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function special_days(){
        return $this->hasMany(SpecialDays::class, 'special_day', 'dt');
    }

    public function leave(){
        return $this->hasOne(LeaveApproval::class);
    }

    public function time_update(){
        return $this->hasOne(TimeUpdate::class);
    }

    public function short_leave(){
        return $this->hasOne(ShortLeave::class);
    }

    public function on_duty(){
        return $this->hasOne(OnDuty::class);
    }

}
