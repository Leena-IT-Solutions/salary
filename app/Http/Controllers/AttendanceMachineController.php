<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SettingsController;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Setting;
use App\Models\SpecialDays;

class AttendanceMachineController extends Controller
{

    // Request Format
    // "tagid=" + tagId + "&tagms=" + tagMs + "&dt=" + dt + "&tim=" + tim
    // https://payroll.sarvodayavidyalay.com/attendance/save?tagid=1234&tagms=SAR24101&dt=2024-06-14&tim=08:00
    // http://localhost:8000/attendance/save?tagid=1234&tagms=LITS0001&dt=2024-08-01&tim=09:00

    public function save(Request $request){
        $employee_code = $request->tagms;
        $tagid = $request->tagid;
        $punch_date = $request->dt;
        $punch_time = $request->tim;
        $time_difference = null;

        $attendance_data = [
            "tm" => $punch_time,
        ];

        $response = [
            "employee" => "",
            "message" => ""
        ];

        $employee = Employee::where('tagid', $tagid)->where('employee_code', $employee_code)->first();

        if(!$employee){
            $employee = Employee::where('tagid', null)->where('employee_code', $employee_code)->first();
            if($employee){
                $employee->update(['tagid' => $tagid]);
            } else {
                $response["message"] = "Invalid Employee";
            }
        }

        if($employee){
            $employee_id = $employee->id;
            $es = EmployeeShift::where('employee_id', $employee_id)->where('dt', $punch_date);
            if($es->exists()){

                $response["employee"] = $employee->first_name;
                $employee_shift = $es->first();
                $employee_attendance = $employee_shift->employee_attendance;
                $punch_count = $employee_attendance->count();

                /* When punch count is great than 0 then we count time difference */
                if($punch_count > 0){
                    $tm1 = strtotime($punch_time);
                    $tm2 = strtotime($employee_shift->employee_attendance()->latest()->first()->tm);
                    $time_difference = $tm1 - $tm2;
                    $time_difference = $time_difference == 0 ? 1 : $time_difference;
                }

                if($time_difference == null || $time_difference > 60){
                    $response["message"] = "Success";
                    $employee_shift->employee_attendance()->create($attendance_data);
                } else {
                    $response["message"] = "Already Exists";
                }

            }
        }

        return response()->json($response);
    }

    // Face Reading Route (no RFID verification)
    // Request Format: employee_code=LITS0001&dt=2024-08-01&tim=09:00
    // http://localhost:8000/attendance/face_save?employee_code=LITS0001&dt=2024-08-01&tim=09:00

    public function faceSave(Request $request){

        $machineToken = Setting::where('key', 'Attendance Machine API Token')->first()?->value;
        $requestToken = $request->bearerToken();

        if ($machineToken && $requestToken !== $machineToken) {
            return response()->json([
                "message" => "Unauthorized: Invalid API Token"
            ], 401);
        }

        $employee_code = $request->employee_code;
        $punch_date = $request->p_date;
        $punch_time = $request->p_time;
        $time_difference = null;

        $attendance_data = [
            "tm" => $punch_time,
        ];

        $response = [
            "employee" => "",
            "message" => ""
        ];

        $employee = Employee::where('employee_code', $employee_code)->first();

        if(!$employee){
            $response["message"] = "Invalid Employee";
        }

        if($employee){
            $employee_id = $employee->id;
            $es = EmployeeShift::where('employee_id', $employee_id)->where('dt', $punch_date);
            if($es->exists()){

                $response["employee"] = $employee->first_name;
                $employee_shift = $es->first();
                $punch_count = $employee_shift->employee_attendance->count();

                if($punch_count > 0){
                    $tm1 = strtotime($punch_time);
                    $tm2 = strtotime($employee_shift->employee_attendance()->latest()->first()->tm);
                    $time_difference = $tm1 - $tm2;
                    $time_difference = $time_difference == 0 ? 1 : $time_difference;
                }

                if($time_difference == null || $time_difference > 60){
                    $response["message"] = "Success";
                    $employee_shift->employee_attendance()->create($attendance_data);
                } else {
                    $response["message"] = "Already Exists";
                }

            }
        }

        return response()->json($response);
    }

    public function evalute(Request $request){

        $employee_ids = $request->employee_ids;
        $on_date = $request->on_date;

        if (is_array($employee_ids)) {
            $settings = new SettingsController();

            // Fetch all shifts for these employees on this date in one query!
            $shifts = EmployeeShift::whereIn('employee_id', $employee_ids)
                ->where('dt', $on_date)
                ->with(['working_shift', 'employee_attendance', 'special_days', 'leave', 'time_update', 'short_leave', 'on_duty'])
                ->get()
                ->keyBy('employee_id');

            foreach($employee_ids as $employee_id){
                $employee_shift = $shifts->get($employee_id);
                if ($employee_shift) {
                    $subReq = new Request();
                    $subReq->on_date = $on_date;
                    $subReq->employee_id = $employee_id;
                    $subReq->employee_shift = $employee_shift;
                    $subReq->settings = $settings;
                    $this->evalute($subReq);
                }
            }
            return response()->json(["message" => "Success"]);
        }

        $employee_id = $request->employee_id;
        
        $employee_shift = $request->employee_shift ?? null;
        
        if (!$employee_shift) {
            $es = EmployeeShift::where('employee_id', $employee_id)->where('dt', $on_date);
            if (!$es->exists()) {
                return;
            }
            $employee_shift = $es
                ->with(['leave', 'on_duty', 'short_leave', 'time_update', 'special_days', 'employee_attendance', 'working_shift'])
                ->first();
        }

        $employee_shift_data = [
            "late" => 0,
            "early" => 0,
            "lop" => 1,
            "status" => "Present",
        ];
        
        /* Employee punch records array */
        $employee_attendance = $employee_shift->employee_attendance;
        /* Total punch count number */
        $punch_count = $employee_attendance->count();

        /* Get Reference of Shift In and Out time */
        $halfday = $employee_shift->special_days->contains('day_type', 'Halfday');
        $working_shift = $employee_shift->working_shift;
        $shift_in = $working_shift->in;
        $shift_out = $halfday ? $working_shift->halfday : $working_shift->out;
        $actual_in = null;
        $actual_out = null;
        $isCalculate = false;

        $isLeave = $this->checkLeave($employee_shift);
        $isOnDuty = $this->checkOnDuty($employee_shift);
        $isTimeUpdate = $this->checkTimeUpdate($employee_shift);
        $isShortLeave = $this->checkShortLeave($employee_shift);
        
        /* Absent */
        if($punch_count == 0){
            $employee_shift_data["status"] = "Absent";
            $employee_shift_data["lop"] = 1;
        }

        $sortedAttendance = $employee_attendance->sortBy('tm');

        /* Present */
        if($punch_count == 1){
            $employee_shift_data["status"] = "Present";
            $employee_shift_data["lop"] = 1;
            $inPunch = $sortedAttendance->first();
            $actual_in = $inPunch ? $inPunch->tm : null;
        }

        /* Working */
        if($punch_count > 1){
            $employee_shift_data["status"] = $halfday ? "Halfday Working" : "Working";
            $inPunch = $sortedAttendance->first();
            $outPunch = $sortedAttendance->last();
            $actual_in = $inPunch ? $inPunch->tm : null;
            $actual_out = $outPunch ? $outPunch->tm : null;
        }

        /* Check Approvals */
        if($isLeave == "Not Found"){
            if($isOnDuty == "Not Found"){
                if($isTimeUpdate == "Not Found"){
                    if($isShortLeave == "Not Found"){

                        if($actual_in && $actual_out){
                            $isCalculate = true;
                        }
                        
                    } else {
                        /* Get In and Out Time */
                        $employee_shift_data["status"] = "Short Leave";
                        if($isShortLeave["is_lop"] == "Yes"){
                            $actual_in = $isShortLeave["in"];
                            $actual_out = $isShortLeave["out"];
                            $isCalculate = true;
                        } else {
                            $employee_shift_data["lop"] = 0;
                        }
                    }
                } else {
                    /* Get In and Out Time */
                    $employee_shift_data["status"] = "Time Update";
                    $actual_in = $isTimeUpdate["in"];
                    $actual_out = $isTimeUpdate["out"];
                    $isCalculate = true;
                }
            } else {
                $employee_shift_data["status"] = "On Duty";
                $employee_shift_data["lop"] = $isOnDuty;
            }
        } else {
            $employee_shift_data["status"] = $isLeave == 0.5 ? "Halfday Leave" : "Leave";
            $employee_shift_data["lop"] = $isLeave;
        }

        /* Calculate Late and Early */
        if($isCalculate){

            /* Calculated Late in Minutes */
            $late = 0;
            if($actual_in > $shift_in){
                $late = (strtotime($actual_in) - strtotime($shift_in)) / 60;
                $employee_shift_data["late"] = $late;
            }

            /* Calculated early coming in minutes */
            $early = 0;
            if($actual_out < $shift_out){
                $early = (strtotime($shift_out) - strtotime($actual_out)) / 60;
                $employee_shift_data["early"] = $early;
            }

            $settings = $request->settings ?? new SettingsController();
            $cycle_day = (strlen($settings->cycle_day) < 2 ? '0' : '').$settings->cycle_day;
            $pay_cycle_from = date('Y-m-'.$cycle_day, strtotime($on_date));
            $pay_cycle_from = $pay_cycle_from > $on_date ? date('Y-m-d', strtotime("- 1 month", strtotime($pay_cycle_from))) : $pay_cycle_from;
            $pay_cycle_to = date('Y-m-d', strtotime('+ 1 month', strtotime($pay_cycle_from)));

            $lop_by_late = 0;
            $actual_late_penalty = 0;
            $lop_by_early = 0;
            $actual_early_penalty = 0;

            $actual_late_days = EmployeeShift::where('dt', '>=', $pay_cycle_from)->where('dt', '<', $on_date)->where('late', '>', 0)->count();
            $actual_late_penalty += $actual_late_days > $settings->late_days || $late > $settings->late_minutes ? $settings->late_penalty : 0;

            if($actual_late_days > $settings->late_days || $late > $settings->late_minutes){
                if($settings->late_prorata == 'Yes'){
                    if($settings->late_hrmin == "Hour"){
                        $shift_time = ((strtotime($shift_out) - strtotime($shift_in)) / 3600);
                        $lop_by_late = ceil($late/60)/ $shift_time;
                    } else {
                        $shift_time = ((strtotime($shift_out) - strtotime($shift_in)) / 60);
                        $lop_by_late = $late/ $shift_time;
                    }
                }
            }

            $actual_early_days = EmployeeShift::where('dt', '>=', $pay_cycle_from)->where('dt', '<', $on_date)->where('early', '>', 0)->count();
            $actual_early_penalty += $actual_early_days > $settings->early_days || $early > $settings->early_minutes ? $settings->early_penalty : 0;

            if($actual_early_days > $settings->early_days || $early > $settings->early_minutes){
                if($settings->early_prorata == 'Yes'){
                    if($settings->early_hrmin == "Hour"){
                        $shift_time = ((strtotime($shift_out) - strtotime($shift_in)) / 3600);
                        $lop_by_early = ceil($early/60)/ $shift_time;
                    } else {
                        $shift_time = ((strtotime($shift_out) - strtotime($shift_in)) / 60);
                        $lop_by_early = $early/ $shift_time;
                    }
                }
            }

            if($employee_shift->short_leave){
                if($employee_shift->short_leave->status == "Approved"){
                    $actual_late_penalty = 0;
                    $actual_early_penalty = 0;
                }
            }

            $employee_shift_data["lop"] = round($lop_by_early + $lop_by_late + $actual_early_penalty + $actual_late_penalty, 2);
        }

        if(sizeof($employee_shift->special_days) > 0){

            $prev_row = EmployeeShift::where('employee_id', $employee_id)->where('dt', date('Y-m-d', strtotime('-1 day', strtotime($on_date))))->first();
            $next_row = EmployeeShift::where('employee_id', $employee_id)->where('dt', date('Y-m-d', strtotime('+1 day', strtotime($on_date))))->first();

            $prev_date = $prev_row ? $prev_row->lop : 1;
            $next_date = $next_row ? $next_row->lop : 1;

            foreach($employee_shift->special_days as $sp_day){
                switch($sp_day->day_type){
                    case "Holiday":
                    $employee_shift_data["status"] = "Holiday";
                    $employee_shift_data["lop"] = ($prev_date == 1 && $next_date == 1) ? 1 : 0;
                    break;
                    case "Weekoff":
                    $employee_shift_data["status"] = "Weekoff";
                    $employee_shift_data["lop"] = ($prev_date == 1 && $next_date == 1) ? 1 : 0;
                    break;
                }
            }
        }

        $employee_shift->update($employee_shift_data);
    }

    private function checkLeave($employee_shift){
        $response["leave"] = "Not Found";
        if($employee_shift->leave){
            $response["leave"] = 1;
            if($employee_shift->leave->status == "Approved" && $employee_shift->leave->is_lop == "No"){
                $response["leave"] = 0;
            }
            if($response["leave"] == 1){
                if($employee_shift->leave->is_halfday == "Yes"){
                    $response["leave"] = 0.5;
                }
            }
        }
        return $response["leave"];
    }

    private function checkOnDuty($employee_shift){
        return $employee_shift->on_duty ? 0 : "Not Found";
    }

    private function checkTimeUpdate($employee_shift){
        if($employee_shift->time_update){
            return [
                "in" => $employee_shift->time_update->in_time,
                "out" => $employee_shift->time_update->out_time,
            ];
        }
        return "Not Found";
    }

    private function checkShortLeave($employee_shift){
        if($employee_shift->short_leave){
            return [
                "in" => $employee_shift->short_leave->in_time,
                "out" => $employee_shift->short_leave->out_time,
                "is_lop" => $employee_shift->short_leave->status == "Approved" && $employee_shift->short_leave->is_lop == "No" ? "No" : "Yes",
            ];
        }
        return "Not Found";
    }




    public function saveOld(Request $request){

        return $this->calculateLOP(1, $request->dt);

        $employee_code = $request->tagms;
        $tagid = $request->tagid;
        $punch_date = $request->dt;
        $punch_time = $request->tim;
        $employee_shift = null;
        $working_shift = null;
        $in = null;
        $out = null;
        $halfday = false;
        $employee_attendance = [];
        $time_difference = null;
        $punch_count = 0;
        $isLate = 0;
        $isEarly = 0;
        $total_late = 0;
        $pay_cycle_from = null;
        $pay_cycle_to = null;
        $actual_late_days = 0;
        $actual_early_days = 0;
        $actual_late_penalty = 0;
        $lop_by_late = 0;
        $actual_early_penalty = 0;
        $lop_by_early = 0;


        $attendance_data = [
            "tm" => $punch_time,
        ];

        $employee_shift_data = [
            "late" => 0,
            "early" => 0,
            "lop" => 1,
            "status" => "Present",
        ];

        $response = [
            "message" => ""
        ];

        // Get Employee By his code
        $employee = Employee::where('tagid', $tagid)->where('employee_code', $employee_code)->first();

        if($employee){
            $employee_id = $employee->id;
            $es = EmployeeShift::where('employee_id', $employee->id)->where('dt', $punch_date);
            if($es->exists()){
                
                $halfday = SpecialDays::where('special_day', $punch_date)->where('day_type', 'Halfday')->exists();
                $employee_shift = $es->first();
                $working_shift = $employee_shift->working_shift;
                $in = $working_shift->in;
                $out = $halfday ? $working_shift->halfday : $working_shift->out;
                $employee_attendance = $employee_shift->employee_attendance;
                $punch_count = $employee_attendance->count();

                /* When punch count is great than 0 then we count time difference */
                if($punch_count > 0){
                    $tm1 = strtotime($punch_time);
                    $tm2 = strtotime($employee_shift->employee_attendance()->latest()->first()->tm);
                    $time_difference = $tm1 - $tm2;
                    $time_difference = $time_difference == 0 ? 1 : $time_difference;
                }

                /* If time difference is great than 60 seconds or null then only we take another entry */
                if($time_difference == null || $time_difference > 60){
                    if($punch_count == 0){
                        /* Count isLate minustes */
                        if($punch_time > $in){
                            $isLate = (strtotime($punch_time) - strtotime($in)) / 60;
                            $employee_shift_data["late"] = $isLate;
                        }
                    } else {
                        if($punch_count > 0){
                            
                            $tm2 = date('H:i:s' ,strtotime($employee_shift->employee_attendance()->orderBy('id', 'asc')->first()->tm));
                            /* Count isLate minustes */
                            if($punch_time > $in){
                                $isLate = (strtotime($tm2) - strtotime($in)) / 60;
                                $employee_shift_data["late"] = $isLate;
                            }
                        }
                        
                        /* Count isEarly minutes */
                        if($punch_time < $out){
                            $isEarly = (strtotime($out) - strtotime($punch_time)) / 60;
                            $employee_shift_data["early"] = $isEarly;
                        }
                        $employee_shift_data["status"] = $halfday ? "Halfday Working" : "Working";

                        $cycle_day = Setting::where('key', 'Salary Cycle Start Date')->first()->value;
                        $late_minutes = Setting::where('key', 'Late Minutes')->first()->value;
                        $late_days = Setting::where('key', 'Late Days')->first()->value;
                        $late_penalty = Setting::where('key', 'Penalty On Late Mark in LOP')->first()->value;
                        $late_hrmin = Setting::where('key', 'On Late Calculate LOP as per')->first()->value;
                        $late_prorata = Setting::where('key', "Calculate Late Day's Salary on Pro-rata basis")->first()->value;
                        $early_minutes = Setting::where('key', 'Early Going Minutes')->first()->value;
                        $early_days = Setting::where('key', 'Early Going Days')->first()->value;
                        $early_penalty = Setting::where('key', 'Penalty On Early Going Mark in LOP')->first()->value;
                        $early_hrmin = Setting::where('key', 'On Early Going Calculate LOP as per')->first()->value;
                        $early_prorata = Setting::where('key', "Calculate Early Going Day's Salary on Pro-rata basis")->first()->value;
    
                        $cycle_day = (strlen($cycle_day) < 2 ? '0' : '').$cycle_day; 
                        $pay_cycle_from = date('Y-m-'.$cycle_day, strtotime($punch_date));
                        $pay_cycle_from = $pay_cycle_from > $punch_date ? date('Y-m-d', strtotime("- 1 month", strtotime($pay_cycle_from))) : $pay_cycle_from;
                        $pay_cycle_to = date('Y-m-d', strtotime('+ 1 month', strtotime($pay_cycle_from)));
    
                        $actual_late_days = EmployeeShift::where('dt', '>=', $pay_cycle_from)->where('dt', '<', $punch_date)->where('late', '>', 0)->count();
                        $actual_late_penalty += $actual_late_days > $late_days || $isLate > $late_minutes ? $late_penalty : 0;
    
                        if($actual_late_days > $late_days || $isLate > $late_minutes){
                            if($late_prorata == 'Yes'){
                                if($late_hrmin == "Hour"){
                                    $shift_time = ((strtotime($out) - strtotime($in)) / 3600);
                                    $lop_by_late = ceil($isLate/60)/ $shift_time;
                                } else {
                                    $shift_time = ((strtotime($out) - strtotime($in)) / 60);
                                    $lop_by_late = $isLate/ $shift_time;
                                }
                            }
                        }
    
                        $actual_early_days = EmployeeShift::where('dt', '>=', $pay_cycle_from)->where('dt', '<', $pay_cycle_to)->where('early', '>', 0)->count();
                        $actual_early_penalty += $actual_early_days > $early_days || $isEarly > $early_minutes ? $early_penalty : 0;
    
                        if($actual_early_days > $early_days || $isEarly > $early_minutes){
                            if($early_prorata == 'Yes'){
                                if($early_hrmin == "Hour"){
                                    $shift_time = ((strtotime($out) - strtotime($in)) / 3600);
                                    $lop_by_early = ceil($isEarly/60)/ $shift_time;
                                } else {
                                    $shift_time = ((strtotime($out) - strtotime($in)) / 60);
                                    $lop_by_early = $isEarly/ $shift_time;
                                }
                            }
                        }
    
                        $employee_shift_data["lop"] = round($lop_by_early + $lop_by_late + $actual_early_penalty + $actual_late_penalty, 2);
                    }


                    /* Time Punching Entry */
                    $employee_shift->update($employee_shift_data);
                    $employee_shift->employee_attendance()->create($attendance_data);
                    $response["message"] = "Success";
                }
            }
        }
        return response()->json($response);
    }

}