<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExemptionAndDeductionController extends Controller
{
    public function exemption_and_deduction(){
        return view('salary_settings.exemption_and_deduction');
    }
}
