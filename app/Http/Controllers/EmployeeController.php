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

use App\Exports\EmployeeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function employee_manager(){
        $departments = Department::orderBy('department', 'asc')->get(['id', 'department']);
        $designations = Designation::orderBy('designation', 'asc')->get(['id', 'designation']);
        return view('employee.employee_manager', compact('departments', 'designations'));
    }

    public function profile($id){
        $employee = Employee::with([
            'employee_work_location.work_location',
            'employee_salary.salary_group.earnings',
            'employee_salary.es_statutories.statutory_compliance.statutory_compliance_conditions',
        ])->find($id);

        $salary_breakup = [
            'earnings' => [],
            'deductions' => [],
            'net_pay' => 0,
            'gross_pay' => 0,
            'ctc' => 0,
            'employer_contribution' => 0
        ];

        if ($employee && $employee->employee_salary) {
            $salary = $employee->employee_salary;
            $salary_breakup['ctc'] = $salary->ctc;
            $salary_breakup['gross_pay'] = $salary->gross_pay;
            $salary_breakup['net_pay'] = $salary->net_pay;
            $salary_breakup['employer_contribution'] = $salary->employer_contribution;

            // 1. Calculate Earnings
            if ($salary->salary_group) {
                foreach ($salary->salary_group->earnings as $earning) {
                    if (!$earning->is_active) continue;

                    $amount = 0;
                    if ($earning->is_basic_pay) {
                        $amount = $salary->basic_pay;
                    } elseif ($earning->calculation === 'CTC') {
                        $amount = ($salary->ctc * $earning->value) / 100;
                    } elseif ($earning->calculation === 'Basic') {
                        $amount = ($salary->basic_pay * $earning->value) / 100;
                    } elseif ($earning->calculation === 'Flat') {
                        $amount = $earning->value;
                    } elseif ($earning->calculation === 'Remaining') {
                        $amount = $salary->remaining_amount;
                    }

                    $salary_breakup['earnings'][] = [
                        'name' => $earning->name,
                        'calculation' => $earning->calculation,
                        'value' => $earning->value,
                        'amount' => round($amount, 2)
                    ];
                }
            }

            // 2. Calculate Deductions (Selected Statutories)
            foreach ($salary->es_statutories as $statutory) {
                $comp = $statutory->statutory_compliance;
                $active_cond = null;
                if ($comp) {
                    foreach ($comp->statutory_compliance_conditions as $cond) {
                        if ($cond->is_active && 
                            ($cond->gender === 'All' || $cond->gender === $employee->gender) &&
                            ($cond->employee_contribution != null && $cond->employee_contribution != 0)) {
                            
                            $salary_amount = 0;
                            if ($cond->salary_type === 'Basic Pay') {
                                $salary_amount = $salary->basic_pay;
                            } elseif ($cond->salary_type === 'CTC') {
                                $salary_amount = $salary->ctc;
                            } elseif ($cond->salary_type === 'Gross Pay') {
                                $salary_amount = $salary->gross_pay;
                            }

                            if ($salary_amount >= ($cond->min_salary ?: 0) && (($cond->max_salary ?: 0) == 0 || $salary_amount <= $cond->max_salary)) {
                                $active_cond = $cond;
                                break;
                            }
                        }
                    }
                }

                if ($active_cond) {
                    $salary_amount = 0;
                    if ($active_cond->salary_type === 'Basic Pay') {
                        $salary_amount = $salary->basic_pay;
                    } elseif ($active_cond->salary_type === 'CTC') {
                        $salary_amount = $salary->ctc;
                    } elseif ($active_cond->salary_type === 'Gross Pay') {
                        $salary_amount = $salary->gross_pay;
                    }

                    if ($active_cond->restrict_salary_for_calculation != null && 
                        $active_cond->restrict_salary_for_calculation != 0 &&
                        $salary_amount > $active_cond->restrict_salary_for_calculation) {
                        $salary_amount = $active_cond->restrict_salary_for_calculation;
                    }

                    $employee_contrib = 0;
                    if ($active_cond->calculation === 'Flat') {
                        $employee_contrib = $active_cond->employee_contribution ?: 0;
                    } elseif ($active_cond->calculation === 'Percentage') {
                        $employee_contrib = ($salary_amount * $active_cond->employee_contribution) / 100;
                    }

                    if ($active_cond->max_employee_contribution != null && $active_cond->max_employee_contribution != 0 && $employee_contrib > $active_cond->max_employee_contribution) {
                        $employee_contrib = $active_cond->max_employee_contribution;
                    }

                    $salary_breakup['deductions'][] = [
                        'name' => $comp->scheme_name,
                        'type' => $comp->abbreviation,
                        'amount' => round($employee_contrib, 2)
                    ];
                }
            }
        }

        $work_locations = WorkLocation::get(['id as val', 'location_name as key']);
        $designations = Designation::get(['id as val', 'designation as key']);
        $departments = Department::get(['id as val', 'department as key']);
        $leave_groups = LeaveGroup::get(['id as val', 'name as key']);
        $salary_groups = SalaryGroup::where('is_active', true)->get(['id as val', 'salary_group_name as key']);
        $services = ServicesComponent::get(['id as val', 'name as key']);
        return view('employee.profile', compact('employee', 'salary_breakup', 'work_locations', 'designations', 'departments', 'leave_groups', 'salary_groups', 'services'));
    }

    protected function getFilteredEmployeesQuery(Request $request){
        $by = $request->get('by', 'id');
        $order = $request->get('order', 'desc');
        $key = $request->get('key');
        $value = $request->get('value');

        $employees = Employee::orderBy($by, $order);

        // Search text filter
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

        // Status filter (Current/Exited)
        if ($request->filled('status')) {
            if ($request->get('status') === 'current') {
                $employees->whereNull('doe');
            } elseif ($request->get('status') === 'exited') {
                $employees->whereNotNull('doe');
            }
        }

        // Department filter
        if ($request->filled('department_id')) {
            $employees->whereHas('employee_department', function ($q) use ($request) {
                $q->where('department_id', $request->get('department_id'))->whereNull('to');
            });
        }

        // Designation filter
        if ($request->filled('designation_id')) {
            $employees->whereHas('employee_designation', function ($q) use ($request) {
                $q->where('designation_id', $request->get('designation_id'))->whereNull('to');
            });
        }

        return $employees;
    }

    public function fetch(Request $request){
        $employees = $this->getFilteredEmployeesQuery($request);
        return $employees->with('employee_department.department')
                         ->with('employee_designation.designation')
                         ->with('employee_work_location.work_location')
                         ->with('employee_salary')
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
            'qualification' => 'nullable|in:No School,Primary,Secondary,Higher secondary,Primary School,Secondary School,High School,Undergraduate,Graduate,Diploma,Masters,Doctorate,Other',
            'degree' => 'nullable|string|max:100',
            'aadhar' => 'nullable|string|max:16',
            'pan' => 'nullable|string|max:16',
            'pf' => 'nullable|string|max:100',
            'old_pf' => 'nullable|string|max:100',
            'uan' => 'nullable|string|max:100',
            'old_uan' => 'nullable|string|max:100',
            'esic' => 'nullable|string|max:100',
            'old_esic' => 'nullable|string|max:100',
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
            'qualification' => 'nullable|in:No School,Primary,Secondary,Higher secondary,Primary School,Secondary School,High School,Undergraduate,Graduate,Diploma,Masters,Doctorate,Other',
            'degree' => 'nullable|string|max:100',
            'aadhar' => 'nullable|string|max:16',
            'pan' => 'nullable|string|max:16',
            'pf' => 'nullable|string|max:100',
            'old_pf' => 'nullable|string|max:100',
            'uan' => 'nullable|string|max:100',
            'old_uan' => 'nullable|string|max:100',
            'esic' => 'nullable|string|max:100',
            'old_esic' => 'nullable|string|max:100',
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

    public function exportExcel(Request $request){
        $fields = $request->get('fields', ['id', 'first_name', 'last_name', 'employee_code', 'email', 'phone']);
        $headings = $request->get('headings', ['Staff ID', 'First Name', 'Last Name', 'Code', 'Email', 'Phone']);

        $employees = $this->getFilteredEmployeesQuery($request)
            ->with([
                'employee_department.department',
                'employee_designation.designation',
                'employee_work_location.work_location'
            ])
            ->get();

        $data = [];
        foreach ($employees as $employee) {
            $row = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'department':
                        $row[] = optional($employee->employee_department)->department ? $employee->employee_department->department->department : '—';
                        break;
                    case 'designation':
                        $row[] = optional($employee->employee_designation)->designation ? $employee->employee_designation->designation->designation : '—';
                        break;
                    case 'work_location':
                        $row[] = optional($employee->employee_work_location)->work_location ? $employee->employee_work_location->work_location->location_name : '—';
                        break;
                    default:
                        $row[] = $employee->$field ?? '—';
                        break;
                }
            }
            $data[] = $row;
        }

        return Excel::download(new EmployeeExport($data, $headings), 'employees_export.xlsx');
    }

    public function exportPdf(Request $request){
        $fields = $request->get('fields', ['id', 'first_name', 'last_name', 'employee_code', 'email', 'phone']);
        $headings = $request->get('headings', ['Staff ID', 'First Name', 'Last Name', 'Code', 'Email', 'Phone']);

        $employees = $this->getFilteredEmployeesQuery($request)
            ->with([
                'employee_department.department',
                'employee_designation.designation',
                'employee_work_location.work_location'
            ])
            ->get();

        $pdf = Pdf::loadView('pdf.employee_list', compact('employees', 'fields', 'headings'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('employees_export.pdf');
    }
}
