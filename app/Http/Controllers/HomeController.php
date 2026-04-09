<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = date("Y-m-d");
        $total_employee = Employee::where('doe', null)->count();
        $present = Employee::where('doe', null)->whereHas('employee_shift', function($q) use($today){
            $q->where('dt', $today)->has('employee_attendance');
        })
        ->count();
        $absent = Employee::where('doe', null)->whereHas('employee_shift', function($q) use($today){
            $q->where('dt', $today)->has('employee_attendance', 0);
        })
        ->count();
        return view('home', compact('total_employee', 'present', 'absent'));
    }
}
