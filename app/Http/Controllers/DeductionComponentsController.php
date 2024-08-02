<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeductionComponentsController extends Controller
{
    public function deduction_components(){
        return view('salary_settings.deduction_components');
    }
}
