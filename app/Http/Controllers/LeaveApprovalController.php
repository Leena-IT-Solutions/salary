<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    public function leave(){
        return view("approvals.leave");
    }
}
