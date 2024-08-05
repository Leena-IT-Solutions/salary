<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApproval;
use App\Models\LeaveMaster;
use App\Models\Employee;
use App\Models\FinancialYear;

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

        $input = $request->all();
        $to = strtotime($request->to);
        $from = strtotime($request->from);
        $datediff = $to - $from;
        $input["no_of_days"] =  round($datediff / (60 * 60 * 24)) + 1;

        if($input["no_of_days"] == 1 && $request->is_halfday == "Yes"){
            $input["no_of_days"] = 0.5;
        }

        return LeaveApproval::create($input);
    }

    public function update(Request $request){
        
        $input = $request->all();
        $to = strtotime($request->to);
        $from = strtotime($request->from);
        $datediff = $to - $from;
        $input["no_of_days"] =  round($datediff / (60 * 60 * 24)) + 1;

        if($input["no_of_days"] == 1 && $request->is_halfday == "Yes"){
            $input["no_of_days"] = 0.5;
        }
        
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
            ->where('from', '>=', $fy->from)
            ->where('from', '<=', $fy->to)
            ->where('to', '>=', $fy->from)
            ->where('to', '<=', $fy->to)
            ->where('status', 'Approved')
            ->sum('no_of_days');

            $data = [
                "id" => $leave_master_id,
                "used" => $used
            ];

            $response["leaves_availed"][] = $data;
        }



        return $response;
    }
}
