<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Payroll;
use App\Models\CompanyProfile;
use App\Models\StatutoryCompliance;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CAExport;
use Maatwebsite\Excel\Facades\Excel;

class PDFController extends Controller
{
    public function demo($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "payroll_".$id.".pdf";
        return Pdf::loadView('pdf.demo', ['company' => $company, 'payroll' => $payroll])->stream($path);
    }

    public function payslip($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "payroll_".$id.".pdf";
        return Pdf::loadView('pdf.payslip', ['company' => $company, 'payroll' => $payroll])->stream($path);
    }

    public function ca_report($id){
        $statutories = StatutoryCompliance::where('is_active', true)->get();

        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "ca_report_".$id.".pdf";
        return Pdf::loadView('pdf.ca_report', [
                'company' => $company,
                'payroll' => $payroll,
                'statutories' => $statutories,
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
