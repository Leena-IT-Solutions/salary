@extends('layouts.pdf')

@section('head')
    <title>CA Report</title>
@endsection

@section('content')

<?php
    function getStaEmployerAmount($statutory, $emp){

        $name = $statutory->scheme_name;

        $sta = $emp
        ->payroll_employee_breakups()
        ->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')
        ->get();

        $amount = 0;
        foreach($sta as $s){
            if($s->name_in_payslip == $name){
                $amount = $s->employer_contribution_amount;
            }
        }

        return $amount;
    }

    function getStaAmount($statutory, $emp){

        $name = $statutory->scheme_name;

        $sta = $emp
        ->payroll_employee_breakups()
        ->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')
        ->get();

        $amount = 0;
        foreach($sta as $s){
            if($s->name_in_payslip == $name){
                $amount = $s->actual_payable_amount;
            }
        }

        return $amount;
    }

    function statuTotal($emp){
        $sta = $emp
        ->payroll_employee_breakups()
        ->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')
        ->with('breakupable', function($q){
            $q->where('statutory_compliance_id', 10);
        })
        ->get();
        $amount = 0;
        foreach($sta as $s){
            $amount += $s->actual_payable_amount;
        }
        return $amount;
    }
?>

<div class="p-5">

    

    <table class="table table-bordered border-dark fs-6">
        
        <tr>
            <th colspan="{{ (sizeof($statutories) * 2) + 15}}">
                <h1 class="text-uppercase fw-bold">{{ $company->company_name }}</h1>
                <h6>{{ $company->address }} {{ $company->city }} {{ $company->pincode }} {{ $company->state }} {{ $company->country }}</h6>
                <h4 class="p-2">Salary for payroll {{ $payroll->payroll_name }}</h4>
            </th>
        </tr>

        <thead class="text-center text-uppercase">
            <tr style="font-size: 10px;">
                <th colspan="5">Basic Information</th>
                <th colspan="3">Days</th>
                <th colspan="4">Earning</th>
                <th colspan="3">Pay Types</th>
                <th colspan="{{ sizeof($statutories) }}">Employee</th>
                <th colspan="2">Deductions</th>
                <th colspan="{{ sizeof($statutories) }}">Employer</th>
                <th colspan="1" class="text-end">Salary</th>
            </tr>
            <tr style="font-size: 10px;">
                <th style="width: 80px;" class="text-center">SR NO</th>
                <th class="">Name</th>
                <th class="">ESIC NO</th>
                <th class="">PF NO</th>
                <th class="">UAN</th>
                <th class="">Total</th>
                <th class="">LOP</th>
                <th class="">Present</th>
                <th class="">Earning</th>
                <th class="">OT</th>
                <th class="">Reim</th>
                <th class="">Loan</th>
                <th class="">Basic</th>
                <th class="">Gross</th>
                <th class="">Grand</th>
                @foreach($statutories as $key => $statutory)
                    <th class="">{{ $statutory->abbreviation }}</th>
                @endforeach
                <th class="">Other</th>
                <th class="">Total</th>
                @foreach($statutories as $key => $statutory)
                    <th class="">{{ $statutory->abbreviation }}</th>
                @endforeach
                <th class=" text-end" style="width: 70px;">Payable</th>
            </tr>
        </thead>
        <tbody>

            <?php
                $total_statutory_compliance_amount = 0;
                $employer_statu_comps = [];
                foreach($statutories as $sss){
                    $arr_employer = [
                        "key" => "Employer Contribution of " . $sss->abbreviation,
                        "val" => 0
                    ];
                    $employer_statu_comps[] = $arr_employer;

                    $arr_employee = [
                        "key" => "Employee Contribution of " . $sss->abbreviation,
                        "val" => 0
                    ];
                    $employee_statu_comps[] = $arr_employee;
                }
            ?>

            @foreach($payroll->payroll_employees as $ind => $emp)
                <tr style="font-size: 10px;">
                    <td class="text-center">{{ $ind + 1 }}</td>
                    <td class="">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                    <td class="text-center">{{ $emp->employee->esic }}</td>
                    <td class="text-center">{{ $emp->employee->pf }}</td>
                    <td class="text-center">{{ $emp->employee->uan }}</td>
                    <td class="text-center">
                    @if($wdc)
                        @if($wdc->value == "Actual Days")
                            {{ $payroll->actual_days }}
                        @elseif($wdc->value == "Working Days")
                            {{ $payroll->working_days }}
                        @endif
                    @endif
                    </td>
                    <td class="text-center">{{ $emp->payroll_employee_attendances->lop }}</td>
                    <td class="text-center">
                    @if($wdc)
                        @if($wdc->value == "Actual Days")
                            {{ $payroll->actual_days - $emp->payroll_employee_attendances->lop }}
                        @elseif($wdc->value == "Working Days")
                            {{ $payroll->working_days - $emp->payroll_employee_attendances->lop }}
                        @endif
                    @endif
                    </td>
                    <td class="text-center">{{ $emp->total_earning }}</td>
                    <td class="text-center">{{ $emp->overtime_earning }}</td>
                    <td class="text-center">{{ $emp->reimbursement }}</td>
                    <td class="text-center">{{ $emp->loan_disbursal }}</td>
                    <td class="text-center">{{ $emp->basic_pay }}</td>
                    <td class="text-center">{{ $emp->gross_pay }}</td>
                    <td class="text-center">{{ $emp->gross_salary }}</td>
                    @foreach($statutories as $key => $statutory)
                        <?php
                            $val = getStaAmount($statutory, $emp);
                            $employee_statu_comps[$key]["val"] += $val;
                            $total_statutory_compliance_amount += $val;
                        ?>
                        <td class="text-center ">{{ $val }}</td>
                    @endforeach
                    <td class="text-center">{{ $emp->gross_deduction - statuTotal($emp) }}</td>
                    <td class="text-center">{{ $emp->gross_deduction }}</td>
                    @foreach($statutories as $key => $statutory)
                        <?php
                            $val = getStaEmployerAmount($statutory, $emp);
                            $employer_statu_comps[$key]["val"] += $val;
                            $total_statutory_compliance_amount += $val;
                        ?>
                        <td class="text-center ">{{ $val }}</td>
                    @endforeach
                    <td class="text-end">{{ $emp->net_payable_amount }}</td>
                </tr>
            @endforeach

            <tr class="fw-bold" style="font-size: 10px;">
                <td class="text-end" colspan="{{ (sizeof($statutories) * 2) + 14}}">Total Payable Amount</td>
                <td class="text-end">Rs {{ $payroll->net_payable_amount }}/-</td>
            </tr>

            @foreach($employer_statu_comps as $sc)
            <tr class="fw-bold" style="font-size: 10px;">
                <td class="text-end" colspan="{{ (sizeof($statutories) * 2) + 14}}">{{ $sc["key"] }}</td>
                <td class="text-end">Rs {{ $sc["val"] }}/-</td>
            </tr>
            @endforeach

            @foreach($employee_statu_comps as $sc)
            <tr class="fw-bold" style="font-size: 10px;">
                <td class="text-end" colspan="{{ (sizeof($statutories) * 2) + 14}}">{{ $sc["key"] }}</td>
                <td class="text-end">Rs {{ $sc["val"] }}/-</td>
            </tr>
            @endforeach

            <tr class="fw-bold" style="font-size: 10px;">
                <td class="text-end" colspan="{{ (sizeof($statutories) * 2) + 14}}">Total payable amount to government for statutory compliance</td>
                <td class="text-end">Rs {{ $total_statutory_compliance_amount }}/-</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection