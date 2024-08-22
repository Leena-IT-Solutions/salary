<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\CompanyProfile;

class PayslipController extends Controller
{
    public function payslip($id){
        $company = CompanyProfile::first();
        $payroll = Payroll::find($id);
        return view('payslip', compact('payroll', 'company'));
    }
}
