<?php

namespace App\Http\Controllers;

use App\Models\OnDuty;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;

class OnDutyController extends Controller
{
    public function on_duty(){
        return view("approvals.on_duty");
    }

    public function fetch(Request $request){
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = OnDuty::with('employee')->orderBy($by, $order);

        if($search){
            $query->where(function($q) use ($search) {
                $q->where('on_date', 'LIKE', "%$search%")
                  ->orWhere('reason', 'LIKE', "%$search%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'LIKE', "%$search%")
                         ->orWhere('last_name', 'LIKE', "%$search%")
                         ->orWhere('employee_code', 'LIKE', "%$search%")
                         ->orWhere('email', 'LIKE', "%$search%")
                         ->orWhere('phone', 'LIKE', "%$search%");
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required',
        ]);

        $from = strtotime($request->from_date);
        $to = strtotime($request->to_date);

        for ($i=$from; $i <= $to; $i = $i + 86400) { 
            $on_date = date('Y-m-d', $i);
            $shift = EmployeeShift::where('dt', $on_date)->where('employee_id', $request->employee_id)->first();
            
            if ($shift) {
                OnDuty::create([
                    "employee_id" => $request->employee_id,
                    "employee_shift_id" => $shift->id,
                    "on_date" => $on_date,
                    "reason" => $request->reason,
                ]);
            }
        }
        return response()->json(['message' => 'On Duty records added successfully']);
    }

    public function update(Request $request){
        $request->validate([
            'on_date' => 'required|date',
            'reason' => 'required',
        ]);

        $input = $request->all();
        $shift = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first();
        if ($shift) {
            $input["employee_shift_id"] = $shift->id;
        }
        
        OnDuty::find($request->id)->update($input);
        return response()->json(['message' => 'On Duty record updated successfully']);
    }

    public function delete(Request $request){
        OnDuty::find($request->id)->delete();
        return response()->json(['message' => 'On Duty record deleted successfully']);
    }

    public function employee($id){
        return [
            "employee" => Employee::where(function($q) use ($id) {
                $q->where('employee_code', $id)
                  ->orWhere('phone', 'LIKE', "%$id%")
                  ->orWhere('email', 'LIKE', "%$id%")
                  ->orWhere('first_name', 'LIKE', "%$id%")
                  ->orWhere('last_name', 'LIKE', "%$id%");
            })->first()
        ];
    }
}
