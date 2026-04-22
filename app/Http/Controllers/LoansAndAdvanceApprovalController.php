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
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = LoanAndAdvanceApproval::with('employee')->orderBy($by, $order);

        if($search){
            $query->where(function($q) use ($search) {
                $q->where('status', 'LIKE', "%$search%")
                  ->orWhere('app_date', 'LIKE', "%$search%")
                  ->orWhere('remark', 'LIKE', "%$search%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'LIKE', "%$search%")
                         ->orWhere('last_name', 'LIKE', "%$search%")
                         ->orWhere('employee_code', 'LIKE', "%$search%")
                         ->orWhere('email', 'LIKE', "%$search%")
                         ->orWhere('phone', 'LIKE', "%$search%");
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'application_date' => 'required|date',
            'loan_amount' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return LoanAndAdvanceApproval::create($request->all());
    }

    public function update(Request $request){
        $request->validate([
            'application_date' => 'required|date',
            'loan_amount' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return LoanAndAdvanceApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        LoanAndAdvanceApproval::find($request->id)->delete();
        return response()->json(['message' => 'Record deleted successfully']);
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
