<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\PayrollEmployee;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        return view('employee.dashboard', ['tab' => 'overview']);
    }

    public function profile()
    {
        return view('employee.dashboard', ['tab' => 'profile']);
    }

    public function payslips()
    {
        return view('employee.dashboard', ['tab' => 'payslips']);
    }

    public function attendance()
    {
        return view('employee.dashboard', ['tab' => 'attendance']);
    }

    public function holidays()
    {
        return view('employee.dashboard', ['tab' => 'holidays']);
    }

    public function requests()
    {
        return view('employee.dashboard', ['tab' => 'requests']);
    }

    public function get_my_data(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)
            ->with([
                'employee_department.department', 
                'employee_designation.designation', 
                'employee_work_location.work_location', 
                'employee_bank',
                'employee_address',
                'employee_photo'
            ])
            ->first();

        if (!$employee) return response()->json(['error' => 'not found'], 404);

        // Parameters for Dashboard temporal navigation
        $month = $request->month ?: date('m');
        $year = $request->year ?: date('Y');

        // Fetch shifts for the specific month with audit relationships
        $shifts = EmployeeShift::where('employee_id', $employee->id)
            ->whereMonth('dt', $month)
            ->whereYear('dt', $year)
            ->with([
                'employee_attendance' => function($q){
                    $q->orderBy('tm', 'asc');
                },
                'working_shift',
                'leave', 
                'time_update', 
                'short_leave', 
                'on_duty', 
                'overtime'
            ])
            ->orderBy('dt', 'asc')
            ->get();

        // Fetch recent payslips with offset support
        $offset = $request->get('offset', 0);
        $payslips = PayrollEmployee::where('employee_id', $employee->id)
            ->with('payroll')
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit(5)
            ->get();

        // Fetch Holidays/Special Days
        $holidays = \App\Models\SpecialDays::orderBy('special_day', 'asc')->get();

        // Fetch Leave Masters for application
        // Fetch leave types assigned to employee's leave group
        $activeGroup = $employee->employee_leave_group;
        if ($activeGroup && $activeGroup->leave_group) {
            $leaveTypes = $activeGroup->leave_group->lgh->map(function($head) {
                return $head->leave_master;
            })->filter()->values();
        } else {
            $leaveTypes = collect();
        }

        // Fetch today's current shift with attendance
        $currentShift = EmployeeShift::where('employee_id', $employee->id)
            ->where('dt', date('Y-m-d'))
            ->with(['working_shift', 'employee_attendance' => function($q){
                $q->orderBy('tm', 'asc');
            }])
            ->first();

        // Fetch Request History
        $leaveRequests = \App\Models\LeaveApproval::where('employee_id', $employee->id)->with('leave_master')->latest()->get()->map(function($r){ 
            $r->type = 'Leave'; 
            $r->date_display = \Carbon\Carbon::parse($r->on_date)->format('d M, Y');
            return $r; 
        });
        $timeRequests = \App\Models\TimeUpdate::where('employee_id', $employee->id)->latest()->get()->map(function($r){ 
            $r->type = 'Time Update'; 
            $r->date_display = \Carbon\Carbon::parse($r->on_date)->format('d M, Y');
            return $r; 
        });
        $shortRequests = \App\Models\ShortLeave::where('employee_id', $employee->id)->latest()->get()->map(function($r){ 
            $r->type = 'Short Leave'; 
            $r->date_display = \Carbon\Carbon::parse($r->on_date)->format('d M, Y');
            return $r; 
        });
        $otRequests = \App\Models\OvertimeApproval::where('employee_id', $employee->id)->latest()->get()->map(function($r){ 
            $r->type = 'Overtime'; 
            $r->date_display = \Carbon\Carbon::parse($r->on_date)->format('d M, Y');
            return $r; 
        });

        $allRequests = $leaveRequests->concat($timeRequests)->concat($shortRequests)->concat($otRequests)->sortByDesc('created_at')->values();

        // Calculate Leave Statistics for the Current Year
        $leaveStats = [];
        if ($activeGroup && $activeGroup->leave_group) {
            $year = date('Y');
            foreach ($activeGroup->leave_group->lgh as $lgh) {
                if ($lgh->leave_master) {
                    $used = \App\Models\LeaveApproval::where('employee_id', $employee->id)
                        ->where('leave_master_id', $lgh->leave_master_id)
                        ->where('status', 'Approved')
                        ->whereYear('on_date', $year)
                        ->selectRaw('SUM(CASE WHEN is_halfday = "Yes" THEN 0.5 ELSE 1 END) as total')
                        ->first()->total ?? 0;

                    $leaveStats[] = [
                        'type' => $lgh->leave_master->leave_type,
                        'total' => $lgh->no_of_leaves,
                        'used' => (float)$used,
                        'balance' => $lgh->no_of_leaves - $used
                    ];
                }
            }
        }

        // Fetch Birthdays This Month
        $currentMonth = date('m');
        $birthdays = \App\Models\Employee::active()
            ->whereMonth('dob', $currentMonth)
            ->with(['employee_photo' => function($q) {
                $q->latest();
            }])
            ->get()
            ->map(function($e){
                return [
                    'name' => $e->first_name . ' ' . $e->last_name,
                    'code' => $e->employee_code,
                    'photo' => $e->employee_photo ? $e->employee_photo->media : null,
                    'birth_date' => \Carbon\Carbon::parse($e->dob)->format('d M'),
                    'day' => (int)\Carbon\Carbon::parse($e->dob)->format('d'),
                ];
            })
            ->sortBy('day')
            ->values();

        // Fetch Current Salary Breakup
        $current_salary = \App\Models\EmployeeSalary::with(['salary_group.earnings', 'es_statutories.statutory_compliance', 'es_statutories.statutory_compliance_condition'])
            ->where('employee_id', $employee->id)
            ->where('effective_from', '<=', date('Y-m-d'))
            ->orderBy('effective_from', 'desc')
            ->first();

        $salary_breakup = null;
        if ($current_salary && $current_salary->salary_group) {
            $earnings_list = [];
            foreach ($current_salary->salary_group->earnings as $earning) {
                if ($earning->is_active && $earning->pay_time == "Fixed" && $earning->is_compensable == 0) {
                    $amount = 0;
                    if ($earning->calculation == "Flat") {
                        $amount = $earning->value;
                    } else if ($earning->calculation == "Basic") {
                        $amount = ($current_salary->basic_pay * $earning->value) / 100;
                    } else if ($earning->calculation == "CTC") {
                        $amount = ($current_salary->ctc * $earning->value) / 100;
                    }
                    $earnings_list[] = [
                        'name' => $earning->name_in_payslip,
                        'amount' => round($amount)
                    ];
                }
            }

            // Add Remaining as Special Allowance
            if ($current_salary->remaining_amount > 0) {
                $earnings_list[] = [
                    'name' => 'Special Allowance',
                    'amount' => round($current_salary->remaining_amount)
                ];
            }

            $salary_breakup = [
                'ctc' => $current_salary->ctc,
                'gross' => $current_salary->gross_pay,
                'net' => $current_salary->net_pay,
                'earnings' => $earnings_list
            ];
        }

        return response()->json([
            'employee' => $employee,
            'shifts' => $shifts,
            'payslips' => $payslips,
            'holidays' => $holidays,
            'leave_types' => $leaveTypes,
            'current_shift' => $currentShift,
            'all_requests' => $allRequests,
            'leave_stats' => $leaveStats,
            'birthdays' => $birthdays,
            'salary_breakup' => $salary_breakup
        ]);
    }

    public function get_payslip_details($id)
    {
        $user = Auth::user();
        $payslip = PayrollEmployee::where('id', $id)
            ->whereHas('employee', function($q) use($user){
                $q->where('email', $user->email);
            })
            ->with([
                'payroll',
                'employee.employee_bank',
                'employee.employee_designation.designation',
                'payroll_employee_attendances',
                'payroll_employee_earnings',
                'payroll_employee_deductions'
            ])
            ->first();

        if (!$payslip) return response()->json(['error' => 'not found'], 404);

        return response()->json([
            'payslip' => $payslip,
            'amount_in_words' => $payslip->amount_str
        ]);
    }
}
