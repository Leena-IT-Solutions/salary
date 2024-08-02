<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReimbursementApprovalController extends Controller
{
    public function reimbursement(){
        return view("approvals.reimbursement");
    }
}
