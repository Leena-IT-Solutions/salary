<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatutoryComplianceController extends Controller
{
    public function statutory_compliance(){
        return view('salary_settings.statutory_compliance');
    }
}
