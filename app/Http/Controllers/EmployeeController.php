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
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $key = $request->get('key');
        $value = $request->get('value');

        $employees = Employee::active()->orderBy($by, $order);

        if($value != null){
            if($key != null && $key != 'all'){
                $employees = $employees->where($key, 'LIKE', '%'.$value.'%');
            } else {
                $employees = $employees->where(function($query) use ($value) {
                    $query->where('first_name', 'LIKE', '%'.$value.'%')
                          ->orWhere('last_name', 'LIKE', '%'.$value.'%')
                          ->orWhere('employee_code', 'LIKE', '%'.$value.'%')
                          ->orWhere('phone', 'LIKE', '%'.$value.'%')
                          ->orWhere('email', 'LIKE', '%'.$value.'%')
                          ->orWhere('id', 'LIKE', '%'.$value.'%');
                });
            }
        }

        return $employees->with('employee_department.department')
                         ->with('employee_designation.designation')
                         ->simplePaginate(25);
    }

    public function add(Request $request){
        $data = $request->all();
        if (!isset($data['nationality']) || $data['nationality'] === null || $data['nationality'] === '') {
            $data['nationality'] = 'Indian';
        }
        if (!isset($data['religion']) || $data['religion'] === null || $data['religion'] === '') {
            $data['religion'] = 'Other';
        }

        $validated = validator($data, [
            'first_name' => 'required|string|max:65',
            'middle_name' => 'nullable|string|max:65',
            'last_name' => 'required|string|max:65',
            'employee_code' => 'required|string|max:65|unique:employees,employee_code',
            'tagid' => 'nullable|string|max:65',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:12',
            'doj' => 'nullable|date',
            'doe' => 'nullable|date',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'blood_group' => 'required|in:O +ve,O -ve,A +ve,A -ve,B +ve,B -ve,AB +ve,AB -ve,HH,Other',
            'religion' => 'required|in:Hindu,Muslim,Christian,Sikh,Buddhist,Jain,Atheist,Other',
            'cast' => 'nullable|string|max:100',
            'subcast' => 'nullable|string|max:100',
            'mothertongue' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'marital_status' => 'required|in:Married,Widowed,Separated,Divorced,Single,Other',
            'qualification' => 'nullable|in:Primary School,Secondary School,High School,Undergraduate,Graduate,Diploma,Masters,Doctorate,Other',
            'degree' => 'nullable|string|max:100',
            'aadhar' => 'nullable|string|max:16',
            'pan' => 'nullable|string|max:16',
            'pf' => 'nullable|string|max:100',
            'uan' => 'nullable|string|max:100',
            'esic' => 'nullable|string|max:100',
        ])->validate();

        return Employee::create($validated);
    }

    public function update(Request $request){
        $data = $request->all();
        if (!isset($data['nationality']) || $data['nationality'] === null || $data['nationality'] === '') {
            $data['nationality'] = 'Indian';
        }
        if (!isset($data['religion']) || $data['religion'] === null || $data['religion'] === '') {
            $data['religion'] = 'Other';
        }

        $validated = validator($data, [
            'id' => 'required|exists:employees,id',
            'first_name' => 'required|string|max:65',
            'middle_name' => 'nullable|string|max:65',
            'last_name' => 'required|string|max:65',
            'employee_code' => 'required|string|max:65|unique:employees,employee_code,' . $request->id,
            'tagid' => 'nullable|string|max:65',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:12',
            'doj' => 'nullable|date',
            'doe' => 'nullable|date',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'blood_group' => 'required|in:O +ve,O -ve,A +ve,A -ve,B +ve,B -ve,AB +ve,AB -ve,HH,Other',
            'religion' => 'required|in:Hindu,Muslim,Christian,Sikh,Buddhist,Jain,Atheist,Other',
            'cast' => 'nullable|string|max:100',
            'subcast' => 'nullable|string|max:100',
            'mothertongue' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'marital_status' => 'required|in:Married,Widowed,Separated,Divorced,Single,Other',
            'qualification' => 'nullable|in:Primary School,Secondary School,High School,Undergraduate,Graduate,Diploma,Masters,Doctorate,Other',
            'degree' => 'nullable|string|max:100',
            'aadhar' => 'nullable|string|max:16',
            'pan' => 'nullable|string|max:16',
            'pf' => 'nullable|string|max:100',
            'uan' => 'nullable|string|max:100',
            'esic' => 'nullable|string|max:100',
        ])->validate();

        $employee = Employee::find($request->id);
        $employee->update($validated);
        return $employee;
    }

    public function delete(Request $request){
        return Employee::find($request->id)->delete();
    }

    public function search(Request $request){
        $q = $request->get('q');
        if(!$q) return [];
        
        return Employee::where('employee_code', 'LIKE', "%$q%")
            ->orWhere('phone', 'LIKE', "%$q%")
            ->orWhere('email', 'LIKE', "%$q%")
            ->orWhere('first_name', 'LIKE', "%$q%")
            ->orWhere('last_name', 'LIKE', "%$q%")
            ->take(10)
            ->get(['id', 'employee_code', 'first_name', 'last_name', 'phone', 'email']);
    }
}
