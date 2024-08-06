<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExemptionAndDeductionApproval;
use App\Models\Employee;
use App\Models\ExeAndDedComponent;

class ExemptionAndDeductionApprovalController extends Controller
{
    public function exemption_and_deduction(){
        $types = ExeAndDedComponent::where('is_active', 1)->orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view("approvals.exemption_and_deduction", compact('types'));
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

        $items = ExemptionAndDeductionApproval::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->with('exe_and_ded_component')->simplePaginate(25);
    }

    public function add(Request $request){
        return ExemptionAndDeductionApproval::create($request->all());
    }

    public function update(Request $request){
        return ExemptionAndDeductionApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return ExemptionAndDeductionApproval::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
