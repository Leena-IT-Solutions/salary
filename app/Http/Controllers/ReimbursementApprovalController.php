<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReimbursementApproval;
use App\Models\Employee;
use App\Models\ReimbursementComponent;

class ReimbursementApprovalController extends Controller
{
    public function reimbursement(){
        $types = ReimbursementComponent::where('is_active', 1)->orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view("approvals.reimbursement", compact('types'));
    }

    public function fetch(Request $request){
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = ReimbursementApproval::with(['employee', 'reimbursement_component'])->orderBy($by, $order);

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
                  ->orWhereHas('reimbursement_component', function($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%$search%");
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'reimbursement_component_id' => 'required',
            'app_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return ReimbursementApproval::create($request->all());
    }

    public function update(Request $request){
        $request->validate([
            'reimbursement_component_id' => 'required',
            'app_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return ReimbursementApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        ReimbursementApproval::find($request->id)->delete();
        return response()->json(['message' => 'Reimbursement deleted successfully']);
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
