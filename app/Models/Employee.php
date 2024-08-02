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

    public function employee_shifts(){
        return $this->hasMany(EmployeeShift::class);
    }
}
