<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Redirect;
use App\Models\Payroll;
use App\Models\CompanyProfile;
use App\Models\StatutoryCompliance;

use App\Exports\CAExport;
use Maatwebsite\Excel\Facades\Excel;

class PDFController extends Controller
{
    public function demo($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "pdfs/payroll_".$id.".pdf";
        Pdf::view('pdf.demo', ['company' => $company, 'payroll' => $payroll])->save($path);
        return redirect("/".$path);
    }

    public function payslip($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "pdfs/payroll_".$id.".pdf";
        Pdf::view('pdf.payslip', ['company' => $company, 'payroll' => $payroll])->save($path);
        // return view('pdf.payslip', compact('payroll', 'company'));
        return redirect("/".$path);
    }

    public function ca_report($id){
        $statutories = StatutoryCompliance::where('is_active', true)->get();

        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "pdfs/ca_report_".$id.".pdf";
        Pdf::view('pdf.ca_report', [
                'company' => $company,
                'payroll' => $payroll,
                'statutories' => $statutories,
            ])
        ->landscape()
        ->save($path);
        // return view('pdf.ca_report', compact('payroll', 'company', 'statutories'));
        return redirect("/".$path);
    }

    public function bank_letter($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        $path = "pdfs/bank_letter_".$id.".pdf";
        Pdf::view('pdf.bank_letter', ['company' => $company, 'payroll' => $payroll])
        ->save($path);
        // return view('pdf.bank_letter', compact('payroll', 'company'));
        return redirect("/".$path);
    }

    public function excel_ca_report($id){
        return Excel::download(new CAExport($id), 'ca_report.xlsx');
    }
}
