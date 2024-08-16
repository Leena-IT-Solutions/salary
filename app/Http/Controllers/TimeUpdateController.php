<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeUpdate;
use App\Models\Employee;
use App\Models\EmployeeShift;

class TimeUpdateController extends Controller
{
    public function time_update(){
        return view("approvals.time_update");
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

        $items = TimeUpdate::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->simplePaginate(25);
    }

    public function add(Request $request){
        $input = $request->all();
        $input["employee_shift_id"] = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first()->id;
        return TimeUpdate::create($input);
    }

    public function update(Request $request){
        $input = $request->all();
        $input["employee_shift_id"] = EmployeeShift::where('dt', $request->on_date)->where('employee_id', $request->employee_id)->first()->id;
        return TimeUpdate::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return TimeUpdate::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
