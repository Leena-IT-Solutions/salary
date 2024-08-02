<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function services(){
        return "Services";
        // return view('salary_settings.deduction_components');
    }
}
