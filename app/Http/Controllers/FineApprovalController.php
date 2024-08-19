<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FineApproval;
use App\Models\Employee;

class FineApprovalController extends Controller
{
    public function index(){
        return view("approvals.fine");
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

        $items = FineApproval::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('employee')->simplePaginate(25);
    }

    public function add(Request $request){
        return FineApproval::create($request->all());
    }

    public function update(Request $request){
        return FineApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return FineApproval::find($request->id)->delete();
    }

    public function employee($id){

        $response = [
            "employee" => null,
        ];
        
        $response["employee"] = Employee::where('employee_code', $id)->first();

        return $response;
    }
}
