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
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('value');

        $query = FineApproval::with('employee')->orderBy($by, $order);

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
                  });
            });
        }

        return $query->simplePaginate(25);
    }

    public function add(Request $request){
        $request->validate([
            'employee_id' => 'required',
            'on_date' => 'required|date',
            'fine_amt' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return FineApproval::create($request->all());
    }

    public function update(Request $request){
        $request->validate([
            'on_date' => 'required|date',
            'fine_amt' => 'required|numeric|min:1',
            'status' => 'required',
        ]);
        return FineApproval::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        FineApproval::find($request->id)->delete();
        return response()->json(['message' => 'Fine deleted successfully']);
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
