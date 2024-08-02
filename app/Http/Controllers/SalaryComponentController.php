<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function salary_component(){
        return view('settings.salary_component');
    }
}
