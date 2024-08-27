<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkLocation;
use App\Models\Department;
use App\Models\Employee;
use App\Http\Controllers\AttendanceMachineController;

use DatePeriod;
use DateTime;
use DateInterval;

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

    public function attendance_evalution_report(){

        $settings = new SettingsController();
        $today = date('Y-m-d');
        $cycle_day = (strlen($settings->cycle_day) < 2 ? '0' : '').$settings->cycle_day;
        $pay_cycle_from = date('Y-m-'.$cycle_day, strtotime($today));
        $from = $today >= $pay_cycle_from ? date('Y-m-d', strtotime("- 1 month", strtotime($pay_cycle_from))) : date('Y-m-d', strtotime("- 2 month", strtotime($pay_cycle_from)));
        $to = date('Y-m-d', strtotime('+ 1 month - 1 day', strtotime($from)));

        return view("attendance_evalution_report", compact("from", "to"));
    }

    public function get_data(Request $request){
        $employees = Employee::query();

        $employees->with('employee_shifts', function($q) use($request){
            $q
            ->with('working_shift')
            ->with('employee_attendance')
            ->where('dt', '>=', $request->from)
            ->where('dt', '<=', $request->to);
        });

        $to = date('Y-m-d', strtotime('+1 day', strtotime($request->to)));

        $period = new DatePeriod(
            new DateTime($request->from),
            new DateInterval('P1D'),
            new DateTime($to)
        );

        $dates = [];
        $dds = [];
        $ddmmyyyys = [];
        foreach ($period as $key => $value) {
            $dates[] = $value->format('Y-m-d');
            $dds[] = $value->format('d');
            $ddmmyyyys[] = $value->format('d-m-Y');
        }

        $employees = $employees
        ->with('employee_work_location.work_location')
        ->with('employee_department.department')
        ->where(function ($q) use($request){
            $q->where('doe', null)->orWhere('doe', '>=', $request->from);
        })
        ->orderBy('first_name', 'asc')
        ->get();

        return [
            "employees" => $employees,
            "dates" => $dates,
            "dds" => $dds,
            "ddmmyyyys" => $ddmmyyyys,
        ];
    }

    public function run_lop(Request $request){
        $period = new DatePeriod(
            new DateTime($request->from),
            new DateInterval('P1D'),
            new DateTime(date('Y-m-d',strtotime('+1 Day', strtotime($request->to))))
        );

        $dates = [];
        foreach ($period as $key => $value) {
            $dates[] = $value->format('Y-m-d');
        }

        foreach($request->eids as $employee_id){
            foreach($dates as $dd){
                $amc = new AttendanceMachineController();
                $req = new Request();
                $req->on_date = $dd;
                $req->employee_id = $employee_id;
                $amc->evalute($req);
            }
        }

        return ["message" => "Success"];
    }
}
