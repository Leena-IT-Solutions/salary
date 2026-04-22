<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShortLeave;
use App\Models\Employee;
use App\Models\EmployeeShift;

class ShortleaveApprovalController extends Controller
{
    public function shortleave(){
        return view("approvals.shortleave");
    }

    public function fetch(Request $request){
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = ShortLeave::with('employee')
            ->select('*', 'in_time as from_time', 'out_time as to_time')
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
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'on_date' => 'required|date',
            'from_time' => 'required',
            'to_time' => 'required',
            'status' => 'required',
        ]);

        $input = $request->except(['from_time', 'to_time']);
        $input['in_time'] = $request->from_time;
        $input['out_time'] = $request->to_time;
        $input['is_lop'] = $input['is_lop'] ?? 'Yes';

        $shift = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first();
        if (!$shift) {
            return response()->json(['errors' => ['on_date' => ['No shift found for this date.']]], 422);
        }
        $input["employee_shift_id"] = $shift->id;
        return ShortLeave::create($input);
    }

    public function update(Request $request){
        $request->validate([
            'on_date' => 'required|date',
            'from_time' => 'required',
            'to_time' => 'required',
            'status' => 'required',
        ]);

        $input = $request->except(['from_time', 'to_time']);
        $input['in_time'] = $request->from_time;
        $input['out_time'] = $request->to_time;
        $input['is_lop'] = $input['is_lop'] ?? 'Yes';

        $shift = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first();
        if ($shift) {
            $input["employee_shift_id"] = $shift->id;
        }
        return ShortLeave::find($request->id)->update($input);
    }

    public function delete(Request $request){
        ShortLeave::find($request->id)->delete();
        return response()->json(['message' => 'Short leave deleted successfully']);
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
