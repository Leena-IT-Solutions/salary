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
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = OnDuty::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->simplePaginate(25);
    }

    public function add(Request $request){
        $from = strtotime($request->from_date);
        $to = strtotime($request->to_date);
        $diff = ($to - $from) / 86400;
        $dates = [];
        for($i=0;$i<=$diff;$i++){
            $dates[] = date('Y-m-d', strtotime("+".$i." days", $from));
        }
        foreach($dates as $on_date){
            $data = [
                "employee_id" => $request->employee_id,
                "employee_shift_id" => EmployeeShift::where('dt', $on_date)->where('employee_id', $request->employee_id)->first()->id,
                "on_date" => $on_date,
                "reason" => $request->reason,
            ];
            OnDuty::create($data);
        }
        return ["message" => "Successful"];
    }

    public function update(Request $request){
        return OnDuty::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return OnDuty::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
