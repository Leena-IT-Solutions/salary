<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OvertimeApprovalController extends Controller
{
    public function overtime(){
        return view("approvals.overtime");
    }
}
