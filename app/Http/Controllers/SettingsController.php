<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SettingsController extends Controller
{
    public $abcd;
    
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    
}
