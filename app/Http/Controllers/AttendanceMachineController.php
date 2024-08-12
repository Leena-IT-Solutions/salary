<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeShift;

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
        $employee_shift = null;
        $working_shift = null;
        $in = null;
        $out = null;
        $halfday = null;
        $employee_attendance = [];
        $time_difference = null;
        $punch_count = 0;
        $isLate = 0;
        $isEarly = 0;
        $total_late = 0;

        $attendance_data = [
            "tm" => $punch_time,
        ];

        $employee_shift_data = [
            "late" => null,
            "early" => null,
            "lop" => 0,
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
                
                $employee_shift = $es->first();
                $working_shift = $employee_shift->working_shift;
                $in = $working_shift->in;
                $out = $working_shift->out;
                $halfday = $working_shift->halfday;
                $employee_attendance = $employee_shift->employee_attendance;
                $punch_count = $employee_attendance->count();

                /* When punch count is great than 0 then we count time difference */
                if($punch_count > 0){
                    $tm1 = strtotime($punch_time);
                    $tm2 = strtotime($employee_attendance->orderBy('tm', 'desc')->first()->tm);
                    $time_difference = $tm1 - $tm2;
                }

                /* If time difference is great than 60 seconds or null then only we take another entry */
                if($time_difference > 60 || $time_difference == null){
                    if($punch_count == 0){
                        /* Count isLate minustes */
                        if($punch_time > $in){
                            $isLate = (strtotime($punch_time) - strtotime($in)) / 60;
                            $employee_shift_data["late"] = $isLate;
                        }
                    } else {
                        /* Count isEarly minutes */
                        if($punch_time < $out){
                            $isEarly = (strtotime($out) - strtotime($punch_time)) / 60;
                            $employee_shift_data["early"] = $isEarly;
                        }
                    }

                    /* Time Punching Entry */
                    // $employee_shift->employee_attendance()->create($attendance_data);
                    $response["message"] = "Success";
                }
            }
        }
        return response()->json($response);
    }
}
