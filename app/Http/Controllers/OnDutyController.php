<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnDutyController extends Controller
{
    public function on_duty(){
        return view("approvals.on_duty");
    }
}
