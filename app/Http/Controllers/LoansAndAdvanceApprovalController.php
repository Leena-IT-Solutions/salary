<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoansAndAdvanceApprovalController extends Controller
{
    public function loan_and_advance(){
        return view("approvals.loans_and_advance");
    }
}
