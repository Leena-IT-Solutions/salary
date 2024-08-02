<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReimbursementController extends Controller
{
    public function reimbursement(){
        return view('salary_settings.reimbursement');
    }
}
