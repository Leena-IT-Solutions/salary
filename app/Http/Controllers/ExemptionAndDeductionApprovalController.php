<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExemptionAndDeductionApprovalController extends Controller
{
    public function exemption_and_deduction(){
        return view("approvals.exemption_and_deduction");
    }
}
