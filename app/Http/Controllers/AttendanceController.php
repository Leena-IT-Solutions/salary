<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkLocation;
use App\Models\Department;
use App\Models\Employee;

class AttendanceController extends Controller
{
    public function attendance(){
        $locations = WorkLocation::get(['id as val', 'location_name as key']);
        $departments = Department::get(['id as val', 'department as key']);
        $today = date("Y-m-d");
        $month = date("m");
        return view("attendance", compact('locations', 'departments', 'today', 'month'));
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

        if($request->report_type == "Daily"){
            $employees->with('employee_shifts', function($q) use($request){
                $q
                ->with('working_shift')
                ->with('employee_attendance')
                ->whereDate('dt', $request->current_date);
            });
        }

        if($request->report_type == "Monthly"){
            $employees->with('employee_shifts', function($q) use($request){
                $q
                ->with('working_shift')
                ->with('employee_attendance')
                ->whereYear('dt', $request->current_year)
                ->whereMonth('dt', $request->current_month);
            });
        }

        return $employees
        ->with('employee_work_location.work_location')
        ->with('employee_department.department')
        ->where(function ($q){
            $today = date("Y-m-d");
            $q->where('doe', null)->orWhere('doe', '>=', $today);
        })
        ->orderBy('first_name', 'asc')
        ->get();
    }
}
