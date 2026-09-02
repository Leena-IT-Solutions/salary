<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkLocation;
use App\Models\Department;
use App\Models\Employee;
use App\Http\Controllers\AttendanceMachineController;
use App\Models\EmployeeShift;
use App\Http\Controllers\SettingsController;
use App\Jobs\EvaluateAttendanceJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
                ->with(['employee_attendance' => function($aq) {
                    $aq->orderBy('tm', 'asc');
                }])
                ->whereDate('dt', $request->current_date);
            });
        }

        if($request->report_type == "Monthly"){
            $employees->with('employee_shifts', function($q) use($request){
                $q
                ->with('working_shift')
                ->with(['employee_attendance' => function($aq) {
                    $aq->orderBy('tm', 'asc');
                }])
                ->whereYear('dt', $request->current_year)
                ->whereMonth('dt', $request->current_month);
            });
        }

        $employeeData = $employees
        ->active()
        ->with('employee_work_location.work_location')
        ->with('employee_department.department')
        ->orderBy('first_name', 'asc')
        ->get();

        $special_day = \App\Models\SpecialDays::where('special_day', $request->current_date)->first();

        return response()->json([
            'employees' => $employeeData,
            'special_day' => $special_day,
            'current_date' => $request->current_date,
            'day_name' => $request->current_date ? date('l', strtotime($request->current_date)) : null,
        ]);
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
            ->with(['employee_attendance' => function($aq) {
                $aq->orderBy('tm', 'asc');
            }])
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
        ->active()
        ->with('employee_work_location.work_location')
        ->with('employee_department.department')
        ->orderBy('first_name', 'asc')
        ->get();

        return [
            "employees" => $employees,
            "dates" => $dates,
            "dds" => $dds,
            "ddmmyyyys" => $ddmmyyyys,
        ];
    }

    public function update_times(Request $request){
        if(isset($request->employee_shift_id) && $request->employee_shift_id){
            $es = \App\Models\EmployeeShift::find($request->employee_shift_id);
        } else {
            $es = \App\Models\EmployeeShift::where('employee_id', $request->employee_id)
                ->where('dt', $request->on_date)
                ->first();
            
            if(!$es){
                // Create minimal shift if missing (using default working shift or just placeholder)
                $employee = Employee::find($request->employee_id);
                $es = new \App\Models\EmployeeShift();
                $es->employee_id = $request->employee_id;
                $es->working_shift_id = $employee->working_shift_id ?? 1; // Fallback to 1 if not set
                $es->dt = $request->on_date;
                $es->save();
            }
        }

        if($es){
            // Remove existing attendance records for this shift
            $es->employee_attendance()->delete();

            // Create In record if exists
            if($request->in_time){
                $es->employee_attendance()->create(['tm' => $request->in_time]);
            }

            // Create Out record if exists
            if($request->out_time){
                $es->employee_attendance()->create(['tm' => $request->out_time]);
            }

            // Trigger evalution
            $amc = new AttendanceMachineController();
            $req = new Request();
            $req->employee_id = $es->employee_id;
            $req->on_date = $es->dt;
            $amc->evalute($req);

            return ["message" => "Success"];
        }
        return response()->json(["message" => "Shift not found"], 404);
    }

    public function delete_times(Request $request){
        $es = \App\Models\EmployeeShift::find($request->employee_shift_id);
        if($es){
            $es->employee_attendance()->delete();

            // Trigger evalution
            $amc = new AttendanceMachineController();
            $req = new Request();
            $req->employee_id = $es->employee_id;
            $req->on_date = $es->dt;
            $amc->evalute($req);

            return ["message" => "Success"];
        }
        return response()->json(["message" => "Shift not found"], 404);
    }

    public function auto_update_all(Request $request){
        $shifts = \App\Models\EmployeeShift::where('dt', $request->on_date)
            ->with('working_shift')
            ->whereDoesntHave('employee_attendance')
            ->get();

        foreach($shifts as $es){
            // Create In record
            $es->employee_attendance()->create(['tm' => $es->working_shift->in]);
            // Create Out record
            $es->employee_attendance()->create(['tm' => $es->working_shift->out]);

            // Trigger evalution for this shift
            $amc = new AttendanceMachineController();
            $req = new Request();
            $req->employee_id = $es->employee_id;
            $req->on_date = $es->dt;
            $amc->evalute($req);
        }

        return ["message" => "Success", "processed" => count($shifts)];
    }

    public function run_lop(Request $request){
        $jobId = Str::uuid()->toString();

        $eids = is_array($request->eids) ? array_values($request->eids) : [];

        Cache::put("attendance_job_{$jobId}", [
            'status' => 'processing',
            'processed' => 0,
            'total' => count($eids),
            'eids' => $eids,
            'from' => $request->from,
            'to' => $request->to,
            'error' => null
        ], 1800);

        return [
            "message" => "Success",
            "jobId" => $jobId
        ];
    }

    public function get_progress(string $jobId) {
        $job = Cache::get("attendance_job_{$jobId}");

        if (!$job) {
            return response()->json([
                'status' => 'completed',
                'processed' => 0,
                'total' => 0
            ]);
        }

        if ($job['status'] === 'completed' || $job['status'] === 'failed') {
            return response()->json($job);
        }

        $processed = $job['processed'] ?? 0;
        $total = $job['total'] ?? 0;
        $eids = $job['eids'] ?? [];
        $chunkSize = 3; // Evaluate 3 employees per progress poll for smooth UI counter updates

        if ($processed < $total && !empty($eids)) {
            $slice = array_slice($eids, $processed, $chunkSize);
            $from = $job['from'];
            $to = $job['to'];

            try {
                $period = new \DatePeriod(
                    new \DateTime($from),
                    new \DateInterval('P1D'),
                    new \DateTime(date('Y-m-d', strtotime('+1 Day', strtotime($to))))
                );

                $dates = [];
                foreach ($period as $value) {
                    $dates[] = $value->format('Y-m-d');
                }

                $settings = new SettingsController();
                $amc = new AttendanceMachineController();

                foreach ($slice as $employee_id) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($employee_id, $dates, $from, $to, $settings, $amc) {
                        $shifts = \App\Models\EmployeeShift::where('employee_id', $employee_id)
                            ->whereBetween('dt', [$from, $to])
                            ->with(['working_shift', 'employee_attendance', 'special_days', 'leave', 'time_update', 'short_leave', 'on_duty'])
                            ->get()
                            ->keyBy('dt');

                        foreach ($dates as $dd) {
                            $employee_shift = $shifts->get($dd);
                            if ($employee_shift) {
                                $req = new Request();
                                $req->merge(['on_date' => $dd, 'employee_id' => $employee_id]);
                                $req->attributes->set('employee_shift', $employee_shift);
                                $req->attributes->set('settings', $settings);
                                $amc->evalute($req);
                            }
                        }
                    });
                    $processed++;
                }

                $job['processed'] = $processed;
                if ($processed >= $total) {
                    $job['status'] = 'completed';
                }

                Cache::put("attendance_job_{$jobId}", $job, 1800);

            } catch (\Exception $e) {
                $job['status'] = 'failed';
                $job['error'] = $e->getMessage();
                Cache::put("attendance_job_{$jobId}", $job, 1800);
            }
        } else {
            $job['status'] = 'completed';
            Cache::put("attendance_job_{$jobId}", $job, 1800);
        }

        return response()->json([
            'status' => $job['status'],
            'processed' => $job['processed'],
            'total' => $job['total'],
            'error' => $job['error'] ?? null,
        ]);
    }
}
