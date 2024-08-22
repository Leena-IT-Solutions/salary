<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AttendanceMachineController;
use App\Models\FinancialYear;
use App\Models\Employee;
use App\Models\SpecialDays;
use App\Models\EmployeeShift;
use App\Models\EmployeeSalary;
use App\Models\OvertimeApproval;
use App\Models\VariablePayApproval;
use App\Models\ReimbursementApproval;
use App\Models\LoanAndAdvanceApproval;
use App\Models\EmployeeService;
use App\Models\FineApproval;
use App\Models\Payroll;
use App\Models\PayrollEmployee;
use App\Models\PayrollEmployeeAttendance;
use App\Models\PayrollEmployeeBreakup;
use DatePeriod;
use DateTime;
use DateInterval;

class RunPayrollController extends Controller
{
    public function run_payroll(){
        $financial_years = FinancialYear::get(['id as val', 'fy_name as key']);
        $settings = new SettingsController();
        $today = date('Y-m-d');
        $cycle_day = (strlen($settings->cycle_day) < 2 ? '0' : '').$settings->cycle_day;
        $pay_cycle_from = date('Y-m-'.$cycle_day, strtotime($today));
        $from = $today >= $pay_cycle_from ? date('Y-m-d', strtotime("- 1 month", strtotime($pay_cycle_from))) : date('Y-m-d', strtotime("- 2 month", strtotime($pay_cycle_from)));
        $to = date('Y-m-d', strtotime('+ 1 month - 1 day', strtotime($from)));
        return view('run_payroll', compact('from', 'to', 'financial_years'));
    }

    public function fetch(Request $request){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = Payroll::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function delete(Request $request){
        Payroll::find($request->id)->delete();
    }

    public function add(Request $request){
        return $this->process_payroll($request);
        //return Payroll::create($request->all());
    }

    public function update(Request $request){
        return $this->process_payroll($request);
        //return Payroll::find($request->id)->update($request->all());
    }

    public function fetch_employees(Request $request){

        $date1=date_create($request->from);
        $date2=date_create($request->to);
        $actual_days=date_diff($date1,$date2)->days + 1;

        $off_days = SpecialDays::where('special_day', '>=', $request->from)
        ->where('special_day', '<=', $request->to)
        ->where(function($q){
            $q->where('day_type', 'Weekoff')->orWhere('day_type', 'Holiday');
        })
        ->count();

        $working_days = $actual_days - $off_days;

        $employees = Employee::where('doj', '<=', $request->to)->where(function($q) use ($request){
            $q->where('doe', null)->orWhere('doe', '>=', $request->from);
        })
        ->with('employee_salaries', function($qq) use($request){
            $qq->where('effective_from', '<=', $request->from)->orderBy('effective_from', 'desc');
        })
        ->get();

        return [
            "employees" => $employees,
            "actual_days" => $actual_days,
            "working_days" => $working_days,
        ];
    }

    public function shift_dates(Request $request){
        $settings = new SettingsController();
        $today = $request->what == "next" ? date('Y-m-d', strtotime("+ 2 month", strtotime($request->from))) : $request->from;
        $cycle_day = (strlen($settings->cycle_day) < 2 ? '0' : '').$settings->cycle_day;
        $pay_cycle_from = date('Y-m-'.$cycle_day, strtotime($today));
        $from = $today >= $pay_cycle_from ? date('Y-m-d', strtotime("- 1 month", strtotime($pay_cycle_from))) : date('Y-m-d', strtotime("- 2 month", strtotime($pay_cycle_from)));
        $to = date('Y-m-d', strtotime('+ 1 month - 1 day', strtotime($from)));
        return [
            "from" => $from,
            "to" => $to,
        ];
    }

    public function process_payroll(Request $request){

        $payroll = [
            "financial_year_id" => $request->financial_year_id,
            "payroll_name" => $request->payroll_name,
            "from" => $request->from,
            "to" => $request->to,
            "working_days" => $request->working_days,
            "actual_days" => $request->actual_days,
            "ctc" => 0,
            "basic_pay" => 0,
            "gross_pay" => 0,
            "total_earning" => 0,
            "overtime_earning" => 0,
            "reimbursement" => 0,
            "loan_disbursal" => 0,
            "gross_salary" => 0,
            "gross_deduction" => 0,
            "net_payable_amount" => 0,
        ];

        $payroll_employee = [];

        $payroll_employee_attendance = [];

        $payroll_employee_breakup = [];

        /* Run LOP for Employee and Pay Cycle */
        // $this->run_lop($request);

        $salary_calculations = [
            "employee_id" => [],
            "earnings" => [],
            "variable_pay" => [],
            "reimbursement" => [],
            "loan_disbursal" => [],
            "services" => [],
            "fine" => [],
            "loan_emi" => [],
            "statutories" => [],
        ];

        foreach($request->eids as $ind => $employee_id){

            $all = [];
    
            $payroll_employee_attendance_data = [
                "payroll_employee_id" => 0,
                "lop" => 0,
                "payable_days" => 0,
                "ot_hours" => 0,
                "ot_amount" => 0,
            ];

            $employee = Employee::find($employee_id);
            $employee_salary = EmployeeSalary::
            with(
                'salary_group.earnings',
                'es_statutories.statutory_compliance',
                'es_statutories.statutory_compliance_condition',
            )
            ->where('employee_id', $employee_id)
            ->where('effective_from', '<=', $request->from)
            ->orderBy('effective_from', 'desc')
            ->first();

            $payroll_employee_data = [
                "payroll_id" => 0,
                "employee_id" => $employee_id,
                "ctc" => $employee_salary->ctc,
                "basic_pay" => 0,
                "gross_pay" => 0,
                "total_earning" => 0,
                "overtime_earning" => 0,
                "reimbursement" => 0,
                "loan_disbursal" => 0,
                "gross_salary" => 0,
                "gross_deduction" => 0,
                "net_payable_amount" => 0,
            ];

            $settings = new SettingsController();
            $wdc = $settings->workingdc;
            $days = ($wdc == "Actual Days") ? $request->actual_days : $request->working_days;

            $salary_calculations["employee_id"][] = $employee_id;

            /* Calculate LOP */
            $lop = $this->calculateLOP($request, $employee_id);
            $payable_days = $days - $lop;
            $k = $payable_days / $days;

            $payroll_employee_attendance_data["lop"] = $lop;
            $payroll_employee_attendance_data["payable_days"] = $payable_days;

            /* Calculate Overtime */
            $ot_hours = $this->calculateOT($request, $employee_id);
            $ot_amount = round($employee_salary->per_hour * $ot_hours);
            $payroll_employee_attendance_data["ot_hours"] = $ot_hours;
            $payroll_employee_attendance_data["ot_amount"] = $ot_amount;

            /* Assign Payroll Employee Attendance Data */
            $payroll_employee_attendance[] = $payroll_employee_attendance_data;

            $all[] = [
                "payroll_employee_id" => 0,
                "breakupable_id" => 0,
                "breakupable_type" => "App\Models\PayrollEmployeeAttendance",
                "name_in_payslip" => "Overtime",
                "standard_amount" => 0,
                "actual_payable_amount" => $ot_amount,
                "employer_contribution_amount" => 0,
            ];

            $payroll_employee_data["overtime_earning"] = $ot_amount;

            /* Calculate Earnings */
            $earnings = $this->calculateEarnings($request, $employee, $employee_salary, $k);
            $salary_calculations["earnings"][] = $earnings;
            foreach($earnings as $eee){
                $all[] = $eee;
            }

            /* Calculate Variable Pay */
            $variable_pay = $this->calculateVariablePay($request, $employee_id, $employee_salary);
            $salary_calculations["variable_pay"][] = $variable_pay;
            foreach($variable_pay as $vvv){
                $all[] = $vvv;
            }

            /* Calculate Reimbursement */
            $reimbursement = $this->calculateReimbursement($request, $employee_id, $employee_salary);
            $salary_calculations["reimbursement"][] = $reimbursement;
            foreach($reimbursement as $rrr){
                $payroll_employee_data["reimbursement"] +=$rrr["actual_payable_amount"];
                $all[] = $rrr;
            }

            /* Calculate Loan Disbursal */
            $loan_disbursal = $this->calculateLoanDisbursal($request, $employee_id);
            $salary_calculations["loan_disbursal"][] = $loan_disbursal;
            foreach($loan_disbursal as $lll){
                $payroll_employee_data["loan_disbursal"] += $lll["actual_payable_amount"];
                $all[] = $lll;
            }

            /* Calculate Services */
            $services = $this->calculateServices($request, $employee_id);
            $salary_calculations["services"][] = $services;
            foreach($services as $sss){
                $payroll_employee_data["gross_deduction"] += $sss["actual_payable_amount"];
                $all[] = $sss;
            }

            /* Calculate Fine */
            $fine = $this->calculateFine($request, $employee_id);
            $salary_calculations["fine"][] = $fine;
            foreach($fine as $fff){
                $payroll_employee_data["gross_deduction"] += $fff["actual_payable_amount"];
                $all[] = $fff;
            }

            /* Calculate Loan EMI */
            $loan_emi = $this->calculateLoanEMI($request, $employee_id);
            $salary_calculations["loan_emi"][] = $loan_emi;
            foreach($loan_emi as $emi){
                $payroll_employee_data["gross_deduction"] += $emi["actual_payable_amount"];
                $all[] = $emi;
            }

            /* Calculate Salary Components */
            $sc =  $this->calculateSalaryComponents($earnings, $variable_pay);
            $payroll_employee_data["basic_pay"] = $sc["basic_pay"];
            $payroll_employee_data["gross_pay"] = $sc["gross_pay"];
            $payroll_employee_data["total_earning"] = $sc["total_earning"];

            /* Calculate Statutory Compliance */
            $statutories = $this->calculateStatutoryCompliance($employee_salary, $payroll_employee_data, $request->from, $k);
            $salary_calculations["statutories"][] = $statutories;
            foreach($statutories as $sta){
                $payroll_employee_data["gross_deduction"] += $sta["actual_payable_amount"];
                $all[] = $sta;
            }

            $payroll_employee_data["gross_salary"] = $payroll_employee_data["total_earning"] + $payroll_employee_data["overtime_earning"] + $payroll_employee_data["reimbursement"] + $payroll_employee_data["loan_disbursal"];

            $payroll_employee_data["net_payable_amount"] = $payroll_employee_data["gross_salary"] - $payroll_employee_data["gross_deduction"];

            $payroll_employee_breakup[$ind] = $all;

            $payroll_employee[] = $payroll_employee_data;

            $payroll["ctc"] += $payroll_employee_data["ctc"];
            $payroll["basic_pay"] += $payroll_employee_data["basic_pay"];
            $payroll["gross_pay"] += $payroll_employee_data["gross_pay"];
            $payroll["total_earning"] += $payroll_employee_data["total_earning"];
            $payroll["overtime_earning"] += $payroll_employee_data["overtime_earning"];
            $payroll["reimbursement"] += $payroll_employee_data["reimbursement"];
            $payroll["loan_disbursal"] += $payroll_employee_data["loan_disbursal"];
            $payroll["gross_salary"] += $payroll_employee_data["gross_salary"];
            $payroll["gross_deduction"] += $payroll_employee_data["gross_deduction"];
            $payroll["net_payable_amount"] += $payroll_employee_data["net_payable_amount"];

        }

        $calculation_data = [
            "payroll" => $payroll,
            "payroll_employee" => $payroll_employee,
            "payroll_employee_attendance" => $payroll_employee_attendance,
            "payroll_employee_breakup" => $payroll_employee_breakup,
        ];

        return $this->save_payroll($calculation_data, $request->id);

    }

    public function save_payroll($data, $id=null){
        if($id){
            $payroll = Payroll::find($id);
            $payroll->update($data["payroll"]);
            foreach($payroll->payroll_employees()->get() as $employee){
                $employee->delete();
            }
        } else {
            $payroll = Payroll::create($data["payroll"]);
        }
        

        foreach($data["payroll_employee"] as $ind => $pe){
            
            /* Employee Record Save */
            $pe["payroll_id"] = $payroll->id;
            $payroll_employee = PayrollEmployee::create($pe);
            $payroll_employee_id = $payroll_employee->id;

            /* Attendance Record Save */
            $pea = $data["payroll_employee_attendance"][$ind];
            $pea["payroll_employee_id"] = $payroll_employee_id;
            $attendance = PayrollEmployeeAttendance::create($pea);

            /* Breakup Record Save */
            $peb = $data["payroll_employee_breakup"][$ind];
            foreach($peb as $breakup){
                if($breakup["breakupable_type"] == "App\Models\PayrollEmployeeAttendance"){
                    $breakup["breakupable_id"] = $attendance->id;
                }
                $breakup["payroll_employee_id"] = $payroll_employee_id;
                $pebreakup = PayrollEmployeeBreakup::create($breakup);
            }
        }
        return Payroll::with('payroll_employees.payroll_employee_breakups.breakupable', 'payroll_employees.payroll_employee_attendances')->find($payroll->id);
    }

    public function run_lop(Request $request){
        $period = new DatePeriod(
            new DateTime($request->from),
            new DateInterval('P1D'),
            new DateTime(date('Y-m-d',strtotime('+1 Day', strtotime($request->to))))
        );

        $dates = [];
        foreach ($period as $key => $value) {
            $dates[] = $value->format('Y-m-d');
        }

        foreach($request->eids as $employee_id){
            foreach($dates as $dd){
                $amc = new AttendanceMachineController();
                $req = new Request();
                $req->on_date = $dd;
                $req->employee_id = $employee_id;
                $amc->evalute($req);
            }
        }
    }

    public function calculateLOP(Request $request, $employee_id){
        return EmployeeShift::where('employee_id', $employee_id)->where('dt', '>=', $request->from)->where('dt', '<=', $request->to)->sum('lop');
    }

    public function calculateOT(Request $request, $employee_id){
        return OvertimeApproval::where('employee_id', $employee_id)
        ->where('status', "Approved")
        ->where('on_date', '>=', $request->from)
        ->where('on_date', '<=', $request->to)
        ->sum('hrs');
    }

    public function calculateEarnings(Request $request, $employee, $employee_salary, $k){

        $salary_group = $employee_salary->salary_group;
        $earnings = $employee_salary->salary_group->earnings;

        $ctc = $employee_salary->ctc;
        $gross_pay = $employee_salary->gross_pay;
        $basic_pay = $employee_salary->basic_pay;
        $per_hour = $employee_salary->per_hour;
        $per_minute = $employee_salary->per_minute;
        $checking_gross_pay = $employee_salary->checking_gross_pay;
        
        $items = [];
        foreach($earnings as $earning){
            if($earning->is_active && $earning->pay_time == "Fixed" && $earning->is_compensable == 0){

                $earning->standard = 0;
                $earning->actual = 0;

                if($earning->calculation == "Flat"){
                    $earning->standard = round($earning->value);
                } else if($earning->calculation == "Basic"){
                    $earning->standard = round(($basic_pay * $earning->value) / 100);
                } else if($earning->calculation == "CTC"){
                    $earning->standard = round(($ctc * $earning->value) / 100);
                }

                if($earning->is_pro_rata){
                    $earning->actual = round($earning->standard * $k);
                } else {
                    $earning->actual = round($earning->standard);
                }

                $payroll_employee_breakup_data = [
                    "payroll_employee_id" => 0,
                    "breakupable_id" => $earning->id,
                    "breakupable_type" => "App\Models\Earning",
                    "name_in_payslip" => $earning->name_in_payslip,
                    "standard_amount" => $earning->standard,
                    "actual_payable_amount" => $earning->actual,
                    "employer_contribution_amount" => 0,
                    "is_basic_pay" => $earning->is_basic_pay,
                    "is_gross_pay" => $earning->is_gross_pay,
                ];

                $items[] = $payroll_employee_breakup_data;
            }
        }
        return $items;
    }

    public function calculateVariablePay(Request $request, $employee_id, $employee_salary){

        $vpas = VariablePayApproval::where('employee_id', $employee_id)
        ->where('app_date', '>=', $request->from)
        ->where('app_date', '<=', $request->to)
        ->with('earning')
        ->get();

        $items = [];
        foreach($vpas as $vpa){
            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $vpa->earning->id,
                "breakupable_type" => "App\Models\Earning",
                "name_in_payslip" => $vpa->earning->name_in_payslip,
                "standard_amount" => 0,
                "actual_payable_amount" => round($vpa->amount),
                "employer_contribution_amount" => 0,
                "is_basic_pay" => $vpa->earning->is_basic_pay,
                "is_gross_pay" => $vpa->earning->is_gross_pay,
            ];
            $items[] = $payroll_employee_breakup_data;
        }
        return $items;
    }

    public function calculateReimbursement(Request $request, $employee_id, $employee_salary){
        $data = ReimbursementApproval::where('employee_id', $employee_id)
        ->where('status', "Approved")
        ->where('app_date', '>=', $request->from)
        ->where('app_date', '<=', $request->to)
        ->with('reimbursement_component')
        ->get();

        $items = [];
        foreach($data as $row){
            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $row->reimbursement_component->id,
                "breakupable_type" => "App\Models\ReimbursementApproval",
                "name_in_payslip" => $row->reimbursement_component->name_in_payslip,
                "standard_amount" => 0,
                "actual_payable_amount" => round($row->amount),
                "employer_contribution_amount" => 0,
            ];
            $items[] = $payroll_employee_breakup_data;
        }
        return $items;
    }

    public function calculateLoanDisbursal(Request $request, $employee_id){
        $data = LoanAndAdvanceApproval::where('employee_id', $employee_id)
        ->where('status', "Approved")
        ->where('disbursed_date', null)
        ->get();

        $items = [];
        foreach($data as $row){
            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $row->id,
                "breakupable_type" => "App\Models\LoanAndAdvanceApproval",
                "name_in_payslip" => "Loan",
                "standard_amount" => 0,
                "actual_payable_amount" => round($row->loan_amount),
                "employer_contribution_amount" => 0,
            ];
            $items[] = $payroll_employee_breakup_data;
        }
        return $items;
    }

    public function calculateServices(Request $request, $employee_id){
        $data = EmployeeService::where('employee_id', $employee_id)
        ->where('from', '<=', $request->to)
        ->where(function($q) use($request){
            $q->where('to', null)->orWhere('to', '>=', $request->from);
        })
        ->with('services_component')
        ->get();

        $items = [];
        foreach($data as $row){
            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $row->services_component->id,
                "breakupable_type" => "App\Models\ServicesComponent",
                "name_in_payslip" => $row->services_component->name_in_payslip,
                "standard_amount" => 0,
                "actual_payable_amount" => round($row->services_component->value),
                "employer_contribution_amount" => 0,
            ];
            $items[] = $payroll_employee_breakup_data;
        }
        return $items;
    }

    public function calculateFine(Request $request, $employee_id){
        $data = FineApproval::where('employee_id', $employee_id)
        ->where('app_date', '>=', $request->from)
        ->where('app_date', '<=', $request->to)
        ->get();

        $items = [];
        foreach($data as $row){
            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $row->id,
                "breakupable_type" => "App\Models\FineApproval",
                "name_in_payslip" => $row->note,
                "standard_amount" => 0,
                "actual_payable_amount" => round($row->amount),
                "employer_contribution_amount" => 0,
            ];
            $items[] = $payroll_employee_breakup_data;
        }
        return $items;
    }

    public function calculateLoanEMI(Request $request, $employee_id){
        $data = LoanAndAdvanceApproval::where('employee_id', $employee_id)
        ->where('status', "Approved")
        ->where('is_pause', "No")
        ->where('disbursed_date', '!=', null)
        ->where('close_date', null)
        ->get();

        $items = [];
        foreach($data as $row){
            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $row->id,
                "breakupable_type" => "App\Models\LoanAndAdvanceApproval",
                "name_in_payslip" => "Loan EMI",
                "standard_amount" => 0,
                "actual_payable_amount" => round($row->emi_amount),
                "employer_contribution_amount" => 0,
            ];
            $items[] = $payroll_employee_breakup_data;
        }
        return $items;
    }

    public Function calculateSalaryComponents($earnings, $variable_pay){

        $beta = [
            "basic_pay" => 0,
            "gross_pay" => 0,
            "total_earning" => 0,
        ];

        foreach($earnings as $earning){
            $beta["total_earning"] += $earning["actual_payable_amount"];
            $beta["basic_pay"] += $earning["is_basic_pay"] ? $earning["actual_payable_amount"] : 0;
            $beta["gross_pay"] += $earning["is_gross_pay"] ? $earning["actual_payable_amount"] : 0;
        }

        foreach($variable_pay as $vp){
            $beta["total_earning"] += $vp["actual_payable_amount"];
            $beta["basic_pay"] += $vp["is_basic_pay"] ? $vp["actual_payable_amount"] : 0;
            $beta["gross_pay"] += $vp["is_gross_pay"] ? $vp["actual_payable_amount"] : 0;
        }

        return $beta;

    }

    public function calculateStatutoryCompliance($employee_salary, $payroll_employee_data, $from, $k){
        $sss = $employee_salary->es_statutories;
        $items = [];
        foreach($sss as $statutory){

            $cond = $statutory->statutory_compliance_condition;
            $comp = $statutory->statutory_compliance;

            $data = [
                "employee_contro" => 0,
                "employer_contro" => 0,
            ];

            // salary_type
            $salary_amount = 0;
            switch($cond->salary_type){
                case "Basic Pay": $salary_amount = $payroll_employee_data["basic_pay"];
                break;
                case "Gross Pay": $salary_amount = $payroll_employee_data["gross_pay"];
                break;
                case "CTC": $salary_amount = $payroll_employee_data["ctc"];
                break;
                case "None": $salary_amount = 0;
                break;
            }

            // restrict_salary_for_calculation
            if(
                $cond->restrict_salary_for_calculation != null && 
                $cond->restrict_salary_for_calculation != 0 &&
                $salary_amount > $cond->restrict_salary_for_calculation
            ) {
                $salary_amount = $cond->restrict_salary_for_calculation;
            }

            // calculation
            if($cond->calculation == "Flat"){
                $data["employee_contro"] = $cond->employee_contribution == null || $cond->employee_contribution == 0 ? 0 : $cond->employee_contribution;
                $data["employer_contro"] = $cond->employer_contribution == null || $cond->employer_contribution == 0 ? 0 : $cond->employer_contribution;
            } else if($cond->calculation == "Percentage"){
                $data["employee_contro"] = $cond->employee_contribution == null || $cond->employee_contribution == 0 ? 0 : ($salary_amount * $cond->employee_contribution) / 100;
                $data["employer_contro"] = $cond->employer_contribution == null || $cond->employer_contribution == 0 ? 0 : ($salary_amount * $cond->employer_contribution) / 100;
            } else if($cond->calculation == "CSV"){
                $data["employee_contro"] = $cond->employee_contribution == null || $cond->employee_contribution == 0 ? 0 : readCSV($cond->employee_contribution, $from);
                $data["employer_contro"] = $cond->employer_contribution == null || $cond->employer_contribution == 0 ? 0 : readCSV($cond->employer_contribution, $from);
            }

            // max_employee_contribution
            if($cond->max_employee_contribution <= $data["employee_contro"] && $cond->max_employee_contribution != null && $cond->max_employee_contribution != 0){
                $data["employee_contro"] = $cond->max_employee_contribution;
            }

            // max_employer_contribution
            if($cond->max_employer_contribution <= $data["employer_contro"]  && $cond->max_employer_contribution != null && $cond->max_employer_contribution != 0){
                $data["employer_contro"] = $cond->max_employer_contribution;
            }

            $data["employee_contro"] = round($data["employee_contro"]);
            $data["employer_contro"] = round($data["employer_contro"]);
            
            $statutory["data"] = $data;

            $payroll_employee_breakup_data = [
                "payroll_employee_id" => 0,
                "breakupable_id" => $statutory->statutory_compliance_condition->id,
                "breakupable_type" => "App\Models\StatutoryComplianceCondition",
                "name_in_payslip" => $statutory->statutory_compliance->scheme_name,
                "standard_amount" => 0,
                "actual_payable_amount" => round($data["employee_contro"]),
                "employer_contribution_amount" => round($data["employer_contro"]),
            ];
            $items[] = $payroll_employee_breakup_data;

        }

        return $items;
    }

    public function readCSV($CSVstr, $from){
        $ind = (date('n', strtotime($from))) - 1;
        $str = $CSVstr;
        $arr = explode(",", $str);
        $num = (float)$arr[$ind];
        return $num;
    }
}
