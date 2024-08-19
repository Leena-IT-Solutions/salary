<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VariablePayApproval;
use App\Models\Employee;
use App\Models\Earning;

class VariablePayApprovalController extends Controller
{
    public function index(){
        $types = Earning::where('is_active', 1)->where('pay_time', 'Variable')->orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view("approvals.variable_pay", compact('types'));
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

        $items = VariablePayApproval::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->with('earning')->simplePaginate(25);
    }

    public function add(Request $request){
        return VariablePayApproval::create($request->all());
    }

    public function update(Request $request){
        return VariablePayApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return VariablePayApproval::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
