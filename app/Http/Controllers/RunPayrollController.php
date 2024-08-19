<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RunPayrollController extends Controller
{
    public function run_payroll(){
        return view('run_payroll');
    }
}
