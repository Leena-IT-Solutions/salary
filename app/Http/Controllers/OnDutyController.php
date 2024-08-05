<?php

namespace App\Http\Controllers;
use App\Models\OnDuty;
use App\Models\Employee;

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
        return OnDuty::create($request->all());
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
