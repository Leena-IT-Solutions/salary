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
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = ExemptionAndDeductionApproval::with(['employee', 'exe_and_ded_component'])->orderBy($by, $order);

        if($search){
            $query->where(function($q) use ($search) {
                $q->where('status', 'LIKE', "%$search%")
                  ->orWhere('app_date', 'LIKE', "%$search%")
                  ->orWhere('note', 'LIKE', "%$search%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'LIKE', "%$search%")
                         ->orWhere('last_name', 'LIKE', "%$search%")
                         ->orWhere('employee_code', 'LIKE', "%$search%")
                         ->orWhere('email', 'LIKE', "%$search%")
                         ->orWhere('phone', 'LIKE', "%$search%");
                  })
                  ->orWhereHas('exe_and_ded_component', function($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%$search%");
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'exe_and_ded_component_id' => 'required',
            'app_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return ExemptionAndDeductionApproval::create($request->all());
    }

    public function update(Request $request){
        $request->validate([
            'exe_and_ded_component_id' => 'required',
            'app_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return ExemptionAndDeductionApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        ExemptionAndDeductionApproval::find($request->id)->delete();
        return response()->json(['message' => 'Exemption/Deduction deleted successfully']);
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
