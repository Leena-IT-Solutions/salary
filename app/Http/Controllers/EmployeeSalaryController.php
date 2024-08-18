<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\EmployeeSalary;
use App\Models\SalaryGroup;

class EmployeeSalaryController extends Controller
{
    public function salary_group($id){
        return SalaryGroup::with(['earnings','services','reimbursements','statutories.statutory_compliance_conditions'])->find($id);
    }

    public function fetch(Request $request, $id){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = EmployeeSalary::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function save(Request $request){
        $es = null;
        if(isset($request->id)){
            $es = EmployeeSalary::find($request->id)->update($request->all());
        } else {
            $es = EmployeeSalary::create($request->all());
        }
        return $es;
    }

    public function delete(Request $request){
        return EmployeeSalary::find($request->id)->delete();
    }
}
