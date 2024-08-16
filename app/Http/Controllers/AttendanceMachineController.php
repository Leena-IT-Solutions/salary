<?php

namespace App\Http\Controllers;

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

        return $this->evaluteRecord(1, $punch_date);

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

    public function evaluteRecord($employee_id, $on_date){
        
        /* Get Employee Data */
        $employee = Employee::find($employee_id);
        
        $es = EmployeeShift::where('employee_id', $employee_id)->where('dt', $on_date);
        
        /* If Employee Shift Exists */
        if($es->exists()){
            
            /* Employee shift row */
            return $employee_shift = $es->first();
            /* Employee punch records array */
            $employee_attendance = $employee_shift->employee_attendance;
            /* Total punch count number */
            $punch_count = $employee_attendance->count();
            
            /* Absent */
            if($punch_count == 0){}

            /* Present */
            if($punch_count == 1){}

            /* Working */
            if($punch_count > 1){}
        }
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

    public function calculateLOP($employee_id, $on_date){

        $employee_shift = null;
        $working_shift = null;
        $in = null;
        $out = null;
        $halfday = false;
        $employee_attendance = [];
        $time_difference = null;
        $punch_count = 0;
        $inPunch = null;
        $outPunch = null;
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

        $employee_shift_data = [
            "late" => 0,
            "early" => 0,
            "lop" => 1,
            "status" => "Present",
        ];

        $response = [
            "message" => ""
        ];

        $employee = Employee::find($employee_id);

        /* IF employee exists */
        if($employee){
            $es = EmployeeShift::where('employee_id', $employee_id)->where('dt', $on_date);
            /* If Employee Shift Exists */
            if($es->exists()){
                $halfday = SpecialDays::where('special_day', $on_date)->where('day_type', 'Halfday')->exists();
                $employee_shift = $es->first();
                $working_shift = $employee_shift->working_shift;
                $in = $working_shift->in;
                $out = $halfday ? $working_shift->halfday : $working_shift->out;
                $employee_attendance = $employee_shift->employee_attendance;
                $punch_count = $employee_attendance->count();

                /* When punch count is great than 0 then we count time difference */
                if($punch_count > 1){
                    $inPunch = $employee_shift->employee_attendance()->first();
                    $outPunch = $employee_shift->employee_attendance()->latest()->first();
                    $tm2 = strtotime($inPunch->tm);
                    $tm1 = strtotime($outPunch->tm);
                    $time_difference = $tm1 - $tm2;
                    $time_difference = $time_difference == 0 ? 1 : $time_difference;
                    $employee_shift_data["status"] = $halfday ? "Halfday Working" : "Working";
                } else if($punch_count == 1){
                    $inPunch = $employee_shift->employee_attendance()->first();
                    $employee_shift_data["status"] = "Present";
                    $employee_shift_data["lop"] = 1;
                } else {
                    $employee_shift_data["status"] = "Absent";
                    $employee_shift_data["lop"] = 1;
                }

                /* Count isLate minutes */
                if($inPunch){
                    if($inPunch->tm > $in){
                        $isLate = (strtotime($inPunch->tm) - strtotime($in)) / 60;
                        $employee_shift_data["late"] = $isLate;
                    }
                }

                /* Count isEarly minutes */
                if($outPunch){
                    if($outPunch->tm < $out){
                        $isEarly = (strtotime($out) - strtotime($outPunch->tm)) / 60;
                        $employee_shift_data["early"] = $isEarly;
                    }
                }

                return $employee_shift_data;
                    
                /* Time Punching Entry */
                // $employee_shift->update($employee_shift_data);
                // $response["message"] = "Success";

            }
        }
    }

}