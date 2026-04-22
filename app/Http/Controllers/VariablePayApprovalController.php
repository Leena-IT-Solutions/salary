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
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = VariablePayApproval::with(['employee', 'earning'])->orderBy($by, $order);

        if($search){
            $query->where(function($q) use ($search) {
                $q->where('app_date', 'LIKE', "%$search%")
                  ->orWhere('note', 'LIKE', "%$search%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'LIKE', "%$search%")
                         ->orWhere('last_name', 'LIKE', "%$search%")
                         ->orWhere('employee_code', 'LIKE', "%$search%")
                         ->orWhere('email', 'LIKE', "%$search%")
                         ->orWhere('phone', 'LIKE', "%$search%");
                  })
                  ->orWhereHas('earning', function($eq) use ($search) {
                      $eq->where('name', 'LIKE', "%$search%");
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'earning_id' => 'required',
            'on_date' => 'required|date',
            'amt' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return VariablePayApproval::create($request->all());
    }

    public function update(Request $request){
        $request->validate([
            'earning_id' => 'required',
            'on_date' => 'required|date',
            'amt' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return VariablePayApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        VariablePayApproval::find($request->id)->delete();
        return response()->json(['message' => 'Variable pay deleted successfully']);
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
