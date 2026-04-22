<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeApproval;
use App\Models\Employee;
use App\Models\EmployeeShift;

class OvertimeApprovalController extends Controller
{
    public function overtime()
    {
        return view("approvals.overtime");
    }

    public function fetch(Request $request)
    {
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = OvertimeApproval::with('employee')->orderBy($by, $order);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'LIKE', "%$search%")
                    ->orWhere('on_date', 'LIKE', "%$search%")
                    ->orWhere('note', 'LIKE', "%$search%")
                    ->orWhere('hrs', 'LIKE', "%$search%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
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

    public function add(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'on_date' => 'required|date',
            'hrs' => 'required|numeric|min:0.5',
            'status' => 'required',
        ]);

        $input = $request->all();
        $shift = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first();
        if (!$shift) {
            return response()->json(['errors' => ['on_date' => ['No shift found for this date.']]], 422);
        }
        $input["employee_shift_id"] = $shift->id;
        return OvertimeApproval::create($input);
    }

    public function update(Request $request)
    {
        $request->validate([
            'on_date' => 'required|date',
            'hrs' => 'required|numeric|min:0.5',
            'status' => 'required',
        ]);

        $input = $request->all();
        $shift = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first();
        if ($shift) {
            $input["employee_shift_id"] = $shift->id;
        }
        return OvertimeApproval::find($request->id)->update($input);
    }

    public function delete(Request $request)
    {
        OvertimeApproval::find($request->id)->delete();
        return response()->json(['message' => 'Overtime deleted successfully']);
    }

    public function employee($id)
    {
        return [
            "employee" => Employee::where(function ($q) use ($id) {
                $q->where('employee_code', $id)
                    ->orWhere('phone', 'LIKE', "%$id%")
                    ->orWhere('email', 'LIKE', "%$id%")
                    ->orWhere('first_name', 'LIKE', "%$id%")
                    ->orWhere('last_name', 'LIKE', "%$id%");
            })->first()
        ];
    }
}
