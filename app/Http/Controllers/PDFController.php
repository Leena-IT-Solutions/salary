<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

use App\Http\Controllers\AttendanceController;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\Payroll;
use App\Models\CompanyProfile;
use App\Models\StatutoryCompliance;
use App\Models\EmployeeShift;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AttendanceMachineController;
use DB;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CAExport;
use Maatwebsite\Excel\Facades\Excel;

use DatePeriod;
use DateTime;
use DateInterval;

class PDFController extends Controller
{
    public function demo($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "payroll_".$id.".pdf";
        return Pdf::loadView('pdf.demo', ['company' => $company, 'payroll' => $payroll])->stream($path);
    }

    public function attendance($from, $to){
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $enddate = date('Y-m-d', strtotime('+1 day', strtotime($to)));

        $period = new DatePeriod(
            new DateTime($from),
            new DateInterval('P1D'),
            new DateTime($enddate)
        );

        $dates = [];
        $dds = [];
        $ddmmyyyys = [];
        foreach ($period as $key => $value) {
            $dates[] = $value->format('Y-m-d');
            $dds[] = $value->format('d');
            $ddmmyyyys[] = $value->format('d-m-Y');
        }

        $query = Employee::active();
        if (request()->has('eids')) {
            $query->whereIn('id', explode(',', request()->get('eids')));
        }

        $employees = $query->with(['employee_shifts' => function($q) use($from, $to){
            $q->whereBetween('dt', [$from, $to])->with('employee_attendance');
        }])->get();

        foreach($employees as $employee) {
            $employee->setRelation('employee_shifts', $employee->employee_shifts->keyBy('dt'));
        }

        $path = "attendance_".$from."-" . $to . ".pdf";
        
        return Pdf::loadView('pdf.attendance', [
            "from" => $from,
            "to" => $to,
            "employees" => $employees,
            "dates" => $dates,
            'dds' => $dds
        ])
        ->setPaper('a3', 'landscape')
        ->stream($path);
    }

    public function individual_attendance($from, $to){
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $eids = request()->get('eids');
        $enddate = date('Y-m-d', strtotime('+1 day', strtotime($to)));

        $period = new DatePeriod(
            new DateTime($from),
            new DateInterval('P1D'),
            new DateTime($enddate)
        );

        $dates = [];
        $dds = [];
        $ddmmyyyys = [];
        foreach ($period as $key => $value) {
            $dates[] = $value->format('Y-m-d');
            $dds[] = $value->format('d');
            $ddmmyyyys[] = $value->format('d-m-Y');
        }

        $query = Employee::active();
        if ($eids) {
            $query->whereIn('id', explode(',', $eids));
        }

        $employees = $query->with([
            'employee_department',
            'employee_department.department',
            'employee_salary',
            'employee_shifts' => function($q) use($from, $to){
                $q->whereBetween('dt', [$from, $to])
                  ->with('working_shift')
                  ->with('leave.leave_master')
                  ->with('time_update')
                  ->with('short_leave')
                  ->with('on_duty')
                  ->with('overtime')
                  ->with(['employee_attendance' => function($aq){
                    $aq->orderBy('tm', 'asc');
                  }]);
            }
        ])->get();

        foreach($employees as $employee) {
            $employee->setRelation('employee_shifts', $employee->employee_shifts->keyBy('dt'));
        }

        $path = "individual_attendance_".$from."-" . $to . ".pdf";
        
        return Pdf::loadView('pdf.individual_attendance', [
            "from" => $from,
            "to" => $to,
            "employees" => $employees,
            "dates" => $dates,
            "ddmmyyyys" => $ddmmyyyys
        ])
        ->setPaper('a4', 'portrait')
        ->stream($path);
    }


    public function payslip($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "payroll_".$id.".pdf";
        return Pdf::loadView('pdf.payslip', ['company' => $company, 'payroll' => $payroll])->stream($path);
    }

    public function single_payslip($id){
        $company = CompanyProfile::first();
        $emp = \App\Models\PayrollEmployee::with('payroll', 'payroll_employee_attendances', 'payroll_employee_breakups')->find($id);
        $payroll = $emp->payroll;
        $path = "payslip_".$emp->employee->employee_code."_".$id.".pdf";
        
        return Pdf::loadView('pdf.single_payslip', [
            'company' => $company, 
            'payroll' => $payroll, 
            'emp' => $emp
        ])->stream($path);
    }

    public function ca_report($id){
        $wdc = Setting::where('key', 'Working Days Consideration')->first();
        $statutories = StatutoryCompliance::where('is_active', true)->get();
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "ca_report_".$id.".pdf";
        return Pdf::loadView('pdf.ca_report', [
                'company' => $company,
                'payroll' => $payroll,
                'statutories' => $statutories,
                'wdc' => $wdc,
            ])
        ->setPaper('A4', 'landscape')
        ->stream($path);
    }

    public function bank_letter($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "bank_letter_".$id.".pdf";
        
        return Pdf::loadView('pdf.bank_letter', ['company' => $company, 'payroll' => $payroll])
        ->stream($path);
    }

    public function cheque_print($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::with(['payroll_employees.employee.employee_bank'])->find($id);
        $path = "cheque_print_".$id.".pdf";
        
        return Pdf::loadView('pdf.cheque_print', ['company' => $company, 'payroll' => $payroll])
        ->setPaper('a4', 'landscape')
        ->stream($path);
    }

    public function employeeProfile($id){
        $employee = Employee::with([
            'employee_photo',
        ])->findOrFail($id);

        $addresses = \App\Models\EmployeeAddress::where('employee_id', $id)->get();
        $work_locations = \App\Models\EmployeeWorkLocation::where('employee_id', $id)->with('work_location')->get();
        $departments = \App\Models\EmployeeDepartment::where('employee_id', $id)->with('department')->get();
        $designations = \App\Models\EmployeeDesignation::where('employee_id', $id)->with('designation')->get();
        $leave_groups = \App\Models\EmployeeLeaveGroup::where('employee_id', $id)->with('leave_group')->get();
        $banks = \App\Models\EmployeeBank::where('employee_id', $id)->get();
        $salaries = \App\Models\EmployeeSalary::where('employee_id', $id)->with('salary_group')->get();
        $services = \App\Models\EmployeeService::where('employee_id', $id)->with('services_component')->get();
        $documents = \App\Models\EmployeeDocument::where('employee_id', $id)->get();
        $educations = \App\Models\EmployeeEducation::where('employee_id', $id)->get();

        $path = "employee_profile_" . $employee->employee_code . ".pdf";

        return Pdf::loadView('pdf.employee_profile', compact(
            'employee',
            'addresses',
            'work_locations',
            'departments',
            'designations',
            'leave_groups',
            'banks',
            'salaries',
            'services',
            'documents',
            'educations'
        ))->stream($path);
    }

    public function openProfilePdf($id){
        $url = route('employee.profile.pdf', $id);
        
        if (class_exists(\Native\Desktop\Facades\Shell::class)) {
            \Native\Desktop\Facades\Shell::openExternal($url);
            return back();
        }
        
        return redirect($url);
    }

    public function excel_ca_report($id){
        return Excel::download(new CAExport($id), 'ca_report.xlsx');
    }
}
