<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReimbursementComponentsController extends Controller
{
    public function reimbursement_components(){
        return view('salary_settings.reimbursement_components');
    }
}
