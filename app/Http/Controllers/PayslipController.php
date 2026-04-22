<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\CompanyProfile;
use App\Models\PayrollEmployee;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayslipEmail;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipController extends Controller
{
    public function payslip($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        return view('payslip', compact('payroll', 'company'));
    }

    public function send_email(Request $request){
        $company = CompanyProfile::first();
        
        if($request->type == 'all'){
            $employees = PayrollEmployee::where('payroll_id', $request->payroll_id)->get();
            $payroll = Payroll::find($request->payroll_id);
        } else {
            $employees = PayrollEmployee::where('id', $request->id)->get();
            $payroll = Payroll::find($employees[0]->payroll_id);
        }

        foreach($employees as $emp){
            if($emp->employee->email){
                $pdf = Pdf::loadView('pdf.single_payslip', [
                    'company' => $company, 
                    'payroll' => $payroll, 
                    'emp' => $emp
                ]);
                
                Mail::to($emp->employee->email)->send(new PayslipEmail($emp, $payroll, $pdf));
            }
        }

        return response()->json(['message' => 'Emails sent successfully!']);
    }
    public function fetch_history(Request $request, $employee_id){
        return PayrollEmployee::where('employee_id', $employee_id)
            ->with('payroll')
            ->orderBy('id', 'desc')
            ->simplePaginate(5);
    }

    public function single_payslip($id){
        $company = CompanyProfile::first();
        $emp = PayrollEmployee::with('payroll', 'payroll_employee_attendances', 'payroll_employee_breakups', 'employee.employee_designation.designation', 'employee.employee_bank')->find($id);
        $payroll = $emp->payroll;
        return view('single_payslip', compact('payroll', 'company', 'emp'));
    }
}
