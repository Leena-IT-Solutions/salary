<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\WorkLocation;
use App\Models\Designation;
use App\Models\Department;
use App\Models\LeaveGroup;
use App\Models\SalaryGroup;
use App\Models\ServicesComponent;

class EmployeeController extends Controller
{
    public function employee_manager(){
        return view('employee.employee_manager');
    }

    public function profile($id){
        $employee = Employee::with('employee_work_location.work_location')->with('employee_salary')->find($id);
        $work_locations = WorkLocation::get(['id as val', 'location_name as key']);
        $designations = Designation::get(['id as val', 'designation as key']);
        $departments = Department::get(['id as val', 'department as key']);
        $leave_groups = LeaveGroup::get(['id as val', 'name as key']);
        $salary_groups = SalaryGroup::where('is_active', true)->get(['id as val', 'salary_group_name as key']);
        $services = ServicesComponent::get(['id as val', 'name as key']);
        return view('employee.profile', compact('employee', 'work_locations', 'designations', 'departments', 'leave_groups', 'salary_groups', 'services'));
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

        $employees = Employee::orderBy($by, $order);
        if($key != null && $value != null){
            $employees = $employees->where($key, 'LIKE', '%'.$value.'%');
        }
        return $employees->with('employee_department.department')->with('employee_designation.designation')->simplePaginate(25);
    }

    public function add(Request $request){
        return Employee::create($request->all());
    }

    public function update(Request $request){
        return Employee::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return Employee::find($request->id)->delete();
    }
}
