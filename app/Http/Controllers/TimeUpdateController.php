<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimeUpdateController extends Controller
{
    public function time_update(){
        return view("approvals.time_update");
    }
}
