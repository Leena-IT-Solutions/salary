<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShortleaveApprovalController extends Controller
{
    public function shortleave(){
        return view("approvals.shortleave");
    }
}
