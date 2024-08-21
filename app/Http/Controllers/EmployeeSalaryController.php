<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\EmployeeSalary;
use App\Models\SalaryGroup;
use App\Models\ESStatutory;

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
        $statutories = $request->statutories;
        $request->request->remove('statutories');
        $employee_salary_id = null;
        $es = null;
        if(isset($request->id)){
            $employee_salary_id = $request->id;
            $es = EmployeeSalary::find($request->id)->update($request->all());
        } else {
            $es = EmployeeSalary::create($request->all());
            $employee_salary_id = $es->id;
        }

        if(isset($request->id)){
            ESStatutory::where('employee_salary_id', $employee_salary_id)->delete();
        }

        foreach($statutories as $s){
            if($s["is"]){
                $data = [
                    "employee_salary_id" => $employee_salary_id,
                    "salary_group_id" => $request->salary_group_id,
                    "statutory_compliance_id" => $s["id"],
                    "statutory_compliance_condition_id" => $s["is_id"]
                ];
                ESStatutory::create($data);
            }
        }

        return $employee_salary_id;
    }

    public function delete(Request $request){
        return EmployeeSalary::find($request->id)->delete();
    }
}
