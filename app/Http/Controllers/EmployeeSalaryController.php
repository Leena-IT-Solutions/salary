<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SalaryGroupData;
use App\Models\EmployeeSalary;

class EmployeeSalaryController extends Controller
{
    public function salary_group_data($id){
        return SalaryGroupData::where("salary_group_id", $id)->orderBy('id', 'asc')->get();
    }

    public function save(Request $request){
        return EmployeeSalary::create($request->all());
    }
}
