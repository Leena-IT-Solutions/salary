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

        $employees = Employee::get();

        $path = "attendance_".$from."-" . $to . ".pdf";
        
        return Pdf::loadView('pdf.attendance', [
            "from" => $from,
            "to" => $to,
            "employees" => $employees,
            'dds' => $dds
        ])
        ->setPaper('a3', 'landscape')
        ->stream($path);
    }

    public function payslip($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "payroll_".$id.".pdf";
        return Pdf::loadView('pdf.payslip', ['company' => $company, 'payroll' => $payroll])->stream($path);
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

    public function excel_ca_report($id){
        return Excel::download(new CAExport($id), 'ca_report.xlsx');
    }
}
