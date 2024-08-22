<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'employee_code',
        'tagid',
        'email',
        'phone',
        'doj',
        'doe',
        'dob',
        'gender',
        'blood_group',
        'religion',
        'cast',
        'subcast',
        'mothertongue',
        'nationality',
        'marital_status',
        'qualification',
        'degree',
        'aadhar',
        'pan',
        'pf',
        'uan',
    ];

    public function employee_photo(){
        return $this->hasOne(EmployeePhoto::class)->orderBy('id', 'desc')->latest();
    }

    public function employee_address(){
        return $this->hasOne(EmployeeAddress::class)->orderBy('id', 'desc')->latest();
    }

    public function employee_shift(){
        return $this->hasOne(EmployeeShift::class)->orderBy('dt', 'desc')->latest();
    }

    public function employee_work_location(){
        return $this->hasOne(EmployeeWorkLocation::class)->where('to', null)->latest();
    }

    public function employee_department(){
        return $this->hasOne(EmployeeDepartment::class)->where('to', null)->latest();
    }

    public function employee_designation(){
        return $this->hasOne(EmployeeDesignation::class)->where('to', null)->latest();
    }

    public function employee_bank(){
        return $this->hasOne(EmployeeBank::class)->orderBy('id', 'desc')->latest();
    }

    

    public function employee_service(){
        return $this->hasOne(EmployeeService::class)->where('to', null)->latest();
    }

    public function employee_shifts(){
        return $this->hasMany(EmployeeShift::class);
    }

    public function employee_leave_groups(){
        return $this->hasMany(EmployeeLeaveGroup::class);
    }

    public function employee_leave_group(){
        $today = date("Y-m-d");
        $this->hasOne(EmployeeLeaveGroup::class)
        ->where(function($q) use($today) {
            $q->where('to', null)->orWhere('to', '>=', $today);
        })
        ->where('from', "<=", $today)
        ->latest();
    }

    public function employee_salaries(){
        return $this->hasMany(EmployeeSalary::class);
    }

    public function employee_salary(){
        $from = date('Y-m-d');
        return $this->hasOne(EmployeeSalary::class)->whereDate('effective_from', '<=', $from)->orderBy('id', 'desc')->latest();
    }
}
