<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanAndAdvanceApproval;
use App\Models\Employee;

class LoansAndAdvanceApprovalController extends Controller
{
    public function loan_and_advance(){
        return view("approvals.loans_and_advance");
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

        $items = LoanAndAdvanceApproval::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->simplePaginate(25);
    }

    public function add(Request $request){
        return LoanAndAdvanceApproval::create($request->all());
    }

    public function update(Request $request){
        return LoanAndAdvanceApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return LoanAndAdvanceApproval::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
