<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\WorkingShift;
use App\Models\WorkLocation;
use App\Models\Department;
use App\Models\EmployeeShift;
use DateTime;
use DatePeriod;
use DateInterval;

class EmployeeShiftController extends Controller
{
    public function employee_shift(){
        $shifts = WorkingShift::get(['id as val', 'name as key']);
        $locations = WorkLocation::get(['id as val', 'location_name as key']);
        $departments = Department::get(['id as val', 'department as key']);
        return view("employee_shift", compact('shifts', 'locations', 'departments'));
    }

    public function fetch(Request $request){
        $employees = Employee::query();
        if(isset($request->department_id)){
            if($request->department_id != '0' && $request->department_id != 0){
                $employees->whereHas('employee_department', function($q) use($request){
                    $q->where('department_id', $request->department_id);
                });
            }
        }
        if(isset($request->work_location_id)){
            if($request->work_location_id != '0' && $request->work_location_id != 0){
                $employees->whereHas('employee_work_location', function($q) use($request){
                    $q->where('work_location_id', $request->work_location_id);
                });
            }
        }
        
        return $employees
        ->with('employee_work_location.work_location')
        ->with('employee_department.department')
        ->with('employee_shift.working_shift')
        ->where(function ($q){
            $today = date("Y-m-d");
            $q->where('doe', null)->orWhere('doe', '>=', $today);
        })
        ->get();
    }

    public function save(Request $request){
        foreach($request->employees as $employee_id){
            $begin = new DateTime($request->from);
            $end = new DateTime($request->to);
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end, DatePeriod::INCLUDE_END_DATE);
            foreach ($period as $dt) {
                $dd = $dt->format("Y-m-d");
                $data = [
                    "dt" => $dd,
                    "employee_id" => $employee_id,
                    "working_shift_id" => $request->working_shift_id,
                ];
                $is = EmployeeShift::where('employee_id', $employee_id)->where('dt', $dd);
                if($is->exists()){
                    $is->update($data);
                } else {
                    EmployeeShift::create($data);
                }
                
            }
        }
        return ["message" => "Success"];
    }
}
