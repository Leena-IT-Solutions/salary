<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeApproval;
use App\Models\Employee;

class OvertimeApprovalController extends Controller
{
    public function overtime(){
        return view("approvals.overtime");
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

        $items = OvertimeApproval::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->simplePaginate(25);
    }

    public function add(Request $request){
        return OvertimeApproval::create($request->all());
    }

    public function update(Request $request){
        return OvertimeApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return OvertimeApproval::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
