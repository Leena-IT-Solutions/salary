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
        if (auth()->user()->role == 'Employee') {
            return redirect('/employee/dashboard');
        }

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

        // Fetch Pending Approval Summary
        $pending_count = \App\Models\LeaveApproval::where('status', 'Pending')->count() +
                        \App\Models\TimeUpdate::where('status', 'Pending')->count() +
                        \App\Models\ShortLeave::where('status', 'Pending')->count() +
                        \App\Models\OvertimeApproval::where('status', 'Pending')->count();

        // Fetch Recent Pending Requests
        $recent_pending = collect();
        
        $leaves = \App\Models\LeaveApproval::with(['employee', 'leave_master'])->where('status', 'Pending')->latest()->take(5)->get()->map(function($r){
            $r->req_type = 'Leave';
            $r->display_details = $r->leave_master ? $r->leave_master->leave_type : 'Leave';
            return $r;
        });
        $times = \App\Models\TimeUpdate::with('employee')->where('status', 'Pending')->latest()->take(5)->get()->map(function($r){
            $r->req_type = 'Time Update';
            $r->display_details = "{$r->in_time} - {$r->out_time}";
            return $r;
        });
        $shorts = \App\Models\ShortLeave::with('employee')->where('status', 'Pending')->latest()->take(5)->get()->map(function($r){
            $r->req_type = 'Short Leave';
            $r->display_details = "{$r->in_time} - {$r->out_time}";
            return $r;
        });
        $ots = \App\Models\OvertimeApproval::with('employee')->where('status', 'Pending')->latest()->take(5)->get()->map(function($r){
            $r->req_type = 'Overtime';
            $r->display_details = "{$r->hrs} Hours";
            return $r;
        });

        $recent_pending = $leaves->concat($times)->concat($shorts)->concat($ots)->sortByDesc('created_at')->take(5);

        // Fetch Birthdays This Month
        $currentMonth = date('m');
        $birthdays = Employee::active()
            ->whereMonth('dob', $currentMonth)
            ->with('employee_photo')
            ->get()
            ->map(function($e){
                $e->birth_date = \Carbon\Carbon::parse($e->dob)->format('d M');
                $e->day = (int)\Carbon\Carbon::parse($e->dob)->format('d');
                return $e;
            })
            ->sortBy('day')
            ->values();

        return view('home', compact('total_employee', 'present', 'absent', 'pending_count', 'recent_pending', 'birthdays'));
    }
}
