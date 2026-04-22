<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveMaster;
use App\Models\FinancialYear;
use App\Models\LeaveApproval;
use App\Models\Employee;
use App\Models\EmployeeShift;

class LeaveApprovalController extends Controller
{
    public function leave(){
        $leaves = LeaveMaster::orderBy('leave_type', 'asc')->get(['id as val', 'leave_type as key']);
        $fys = FinancialYear::orderBy('id', 'desc')->get(['id as val', 'fy_name as key']);
        $fy = FinancialYear::where('is_current_year', 'Yes')->first();
        return view("approvals.leave", compact('leaves', 'fys', 'fy'));
    }

    public function fetch(Request $request){
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = LeaveApproval::with(['employee', 'leave_master'])
            ->orderBy($by, $order);

        if($search){
            $query->where(function($q) use ($search) {
                $q->where('status', 'LIKE', "%$search%")
                  ->orWhere('on_date', 'LIKE', "%$search%")
                  ->orWhere('reason', 'LIKE', "%$search%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'LIKE', "%$search%")
                         ->orWhere('last_name', 'LIKE', "%$search%")
                         ->orWhere('employee_code', 'LIKE', "%$search%")
                         ->orWhere('email', 'LIKE', "%$search%")
                         ->orWhere('phone', 'LIKE', "%$search%");
                  })
                  ->orWhereHas('leave_master', function($lq) use ($search) {
                      $lq->where('leave_type', 'LIKE', "%$search%");
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'leave_master_id' => 'required',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'status' => 'required',
            'is_halfday' => 'required',
            'is_lop' => 'required',
        ]);

        $input = $request->except(['from', 'to']);
        $from = strtotime($request->from);
        $to = strtotime($request->to);

        for ($i=$from; $i <= $to; $i = $i + 86400) { 
            $on_date = date('Y-m-d', $i);
            $shift = EmployeeShift::where('dt', $on_date)->where('employee_id', $request->employee_id)->first();
            
            if ($shift) {
                $data = $input;
                $data["on_date"] = $on_date;
                $data["employee_shift_id"] = $shift->id;
                LeaveApproval::create($data);
            }
        }
        return response()->json(['message' => 'Leave added successfully']);
    }

    public function update(Request $request){
        $request->validate([
            'leave_master_id' => 'required',
            'on_date' => 'required|date',
            'status' => 'required',
            'is_halfday' => 'required',
            'is_lop' => 'required',
        ]);

        $input = $request->all();
        $shift = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first();
        if ($shift) {
            $input["employee_shift_id"] = $shift->id;
        }
        
        LeaveApproval::find($request->id)->update($input);
        return response()->json(['message' => 'Leave updated successfully']);
    }

    public function delete(Request $request){
        LeaveApproval::find($request->id)->delete();
        return response()->json(['message' => 'Leave deleted successfully']);
    }

    public function employee($id, $fyid){
        $fy = FinancialYear::find($fyid);
        if (!$fy) {
            return response()->json(['error' => 'Financial year not found'], 404);
        }

        $employee = Employee::where(function($q) use ($id) {
                $q->where('employee_code', $id)
                  ->orWhere('phone', 'LIKE', "%$id%")
                  ->orWhere('email', 'LIKE', "%$id%")
                  ->orWhere('first_name', 'LIKE', "%$id%")
                  ->orWhere('last_name', 'LIKE', "%$id%");
            })->first();
        
        $leaves_availed = [];
        if($employee){
            // Fetch the leave group active during this financial year
            $elg = \App\Models\EmployeeLeaveGroup::with('leave_group.lgh.leave_master')
                ->where('employee_id', $employee->id)
                ->where('from', '<=', $fy->to)
                ->where(function($q) use ($fy) {
                    $q->where('to', null)->orWhere('to', '>=', $fy->from);
                })
                ->latest()
                ->first();
            
            if($elg && $elg->leave_group){
                // Manually set the relation for the frontend
                $employee->setRelation('employee_leave_group', $elg);

                foreach($elg->leave_group->lgh as $lgh){
                    // More robust calculation
                    $query = LeaveApproval::where('employee_id', $employee->id)
                        ->where('leave_master_id', $lgh->leave_master_id)
                        ->where('status', 'LIKE', '%Approved%');
                    
                    // Use whereDate for cleaner comparison
                    if($fy->from && $fy->to){
                        $query->whereDate('on_date', '>=', $fy->from)
                              ->whereDate('on_date', '<=', $fy->to);
                    }

                    $used = $query->selectRaw('SUM(CASE WHEN is_halfday = "Yes" THEN 0.5 ELSE 1 END) as total')
                        ->first()->total ?? 0;
                    
                    $leaves_availed[] = [
                        "id" => $lgh->leave_master_id,
                        "used" => (float)$used
                    ];
                }
            } else {
                // If no leave group found for this FY, set it to NULL
                $employee->setRelation('employee_leave_group', null);
            }
        }

        return [
            "employee" => $employee,
            "leaves_availed" => $leaves_availed
        ];
    }
}
