<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApproval;
use App\Models\LeaveMaster;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\EmployeeShift;

class LeaveApprovalController extends Controller
{
    public function leave(){
        $leaves = LeaveMaster::orderBy('leave_type', 'asc')->get(['id as val', 'leave_type as key']);
        $fys = FinancialYear::orderBy('id', 'desc')->get(['id as val', 'fy_name as key']);
        return view("approvals.leave", compact('leaves', 'fys'));
    }

    public function fetch(Request $request){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $leave_approvals = LeaveApproval::orderBy($by, $order);
        if($key != null && $value != null){
            $leave_approvals = $leave_approvals->where($key, 'LIKE', '%'.$value.'%');
        }
        return $leave_approvals->with('employee','leave_master')->simplePaginate(25);
    }

    public function add(Request $request){

        $from = strtotime($request->from);
        $to = strtotime($request->to);
        $diff = ($to - $from) / 86400;
        $dates = [];
        for($i=0;$i<=$diff;$i++){
            $dates[] = date('Y-m-d', strtotime("+".$i." days", $from));
        }

        foreach($dates as $on_date){
            $data = [
                "employee_id" => $request->employee_id,
                "leave_master_id" => $request->leave_master_id,
                "employee_shift_id" => EmployeeShift::where('dt', $on_date)->where('employee_id', $request->employee_id)->first()->id,
                "on_date" => $on_date,
                "reason" => $request->reason,
                "status" => $request->status,
                "is_halfday" => $request->is_halfday,
                "is_lop" => $request->is_lop,
            ];
            LeaveApproval::create($data);
        }
        return ["message" => "Successful"];
    }

    public function update(Request $request){
        $input = $request->all();
        $input["employee_shift_id"] = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first()->id;
        return LeaveApproval::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return LeaveApproval::find($request->id)->delete();
    }

    public function employee($id, $fyid){

        $fy = FinancialYear::find($fyid);

        $response = [
            "employee" => null,
            "leaves_availed" => []
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)
        ->with('employee_leave_group.leave_group.lgh.leave_master')
        ->first();

        $lghs = $response['employee']->employee_leave_group->leave_group->lgh()->get();

        foreach($lghs as $lgh){
            
            $leave_master_id = $lgh->leave_master_id;
            $used = LeaveApproval::
            where('employee_id', $response["employee"]->id)
            ->where('leave_master_id', $leave_master_id)
            ->where('on_date', '>=', $fy->from)
            ->where('on_date', '<=', $fy->to)
            ->where('is_halfday', 'No')
            ->where('is_lop', 'No')
            ->where('status', 'Approved')
            ->count();

            $usedHalfdays = LeaveApproval::
            where('employee_id', $response["employee"]->id)
            ->where('leave_master_id', $leave_master_id)
            ->where('on_date', '>=', $fy->from)
            ->where('on_date', '<=', $fy->to)
            ->where('is_halfday', 'Yes')
            ->where('is_lop', 'No')
            ->where('status', 'Approved')
            ->count();

            $usedHalfdays = $usedHalfdays * 0.5;

            $data = [
                "id" => $leave_master_id,
                "used" => $used + $usedHalfdays
            ];

            $response["leaves_availed"][] = $data;
        }

        return $response;
    }
}
