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

    <div class="container-fluid text-center mb-3">
        <h1 class="text-uppercase fw-bold text-primary">{{ $company->company_name }}</h1>
        <h6>{{ $company->address }} {{ $company->city }} {{ $company->pincode }} {{ $company->state }} {{ $company->country }}</h6>
        <h4>Salary for payroll {{ $payroll->payroll_name }}</h4>
    </div>

    <table class="table table-bordered border-dark fs-6">
        <thead class="text-center text-uppercase">
            <tr style="font-size: 10px;">
                <th colspan="3">Basic Information</th>
                <th colspan="4">Earning</th>
                <th colspan="3">Pay Types</th>
                <th colspan="{{ sizeof($statutories) }}">Employee</th>
                <th colspan="2">Deductions</th>
                <th colspan="{{ sizeof($statutories) }}">Employer</th>
                <th colspan="1" class="text-end">Salary</th>
            </tr>
            <tr style="font-size: 10px;">
                <th style="width: 80px;" class="text-nowrap text-center">SR NO</th>
                <th class="text-nowrap">name</th>
                <th class="text-nowrap">LOP</th>
                <th class="text-nowrap">Earning</th>
                <th class="text-nowrap">OT</th>
                <th class="text-nowrap">Reim</th>
                <th class="text-nowrap">Loan</th>
                <th class="text-nowrap">Basic</th>
                <th class="text-nowrap">Gross</th>
                <th class="text-nowrap">Grand</th>
                @foreach($statutories as $key => $statutory)
                    <th class="text-nowrap">{{ $statutory->abbreviation }}</th>
                @endforeach
                <th class="text-nowrap">Other</th>
                <th class="text-nowrap">Total</th>
                @foreach($statutories as $key => $statutory)
                    <th class="text-nowrap">{{ $statutory->abbreviation }}</th>
                @endforeach
                <th class="text-nowrap text-end" style="width: 200px;">Payable</th>
            </tr>
        </thead>
        <tbody>

            @foreach($payroll->payroll_employees as $ind => $emp)
                <tr style="font-size: 10px;">
                    <td class="text-center">{{ $ind + 1 }}</td>
                    <td class="text-nowrap">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                    <td class="text-center">{{ $emp->payroll_employee_attendances->lop }}</td>
                    <td class="text-center">{{ $emp->total_earning }}</td>
                    <td class="text-center">{{ $emp->overtime_earning }}</td>
                    <td class="text-center">{{ $emp->reimbursement }}</td>
                    <td class="text-center">{{ $emp->loan_disbursal }}</td>
                    <td class="text-center">{{ $emp->basic_pay }}</td>
                    <td class="text-center">{{ $emp->gross_pay }}</td>
                    <td class="text-center">{{ $emp->gross_salary }}</td>
                    @foreach($statutories as $key => $statutory)
                        <td class="text-center text-nowrap">{{ getStaAmount($statutory, $emp) }}</td>
                    @endforeach
                    <td class="text-center">{{ $emp->gross_deduction - statuTotal($emp) }}</td>
                    <td class="text-center">{{ $emp->gross_deduction }}</td>
                    @foreach($statutories as $key => $statutory)
                        <td class="text-center text-nowrap">{{ getStaEmployerAmount($statutory, $emp) }}</td>
                    @endforeach
                    <td class="text-end">{{ $emp->net_payable_amount }}</td>
                </tr>
            @endforeach

            <tr class="fw-bold">
                <td style="font-size: 10px;" class="text-end" colspan="{{ (sizeof($statutories) * 2) + 13}}">Total Payable Amount &#8377; {{ $payroll->net_payable_amount }}/-</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection