@extends('layouts.newapp')

@section('head')
<title>Payslip for {{ $payroll->payroll_name }}</title>
@endsection

@section('content')

<page-header title="Payslip for {{ $payroll->payroll_name }}"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    @foreach($payroll->payroll_employees as $emp)
    <table class="table border border-dark border-3 m-0">
        <thead>
            <tr>
                <th style="width: 120px;" class="text-center align-middle">
                    <img style="width: 100px;" src="{{ $company->logo }}" alt="{{ $company->company_name }}">
                </th>
                <th colspan="3" class="text-center align-middle">
                    <h3 class="fw-bold">{{ $company->company_name }}</h3>
                    <p class="m-0 fw-light">{{ $company->address }} {{ $company->city }} {{ $company->pincode }} {{ $company->state }} {{ $company->country }}</p>
                    <p class="m-0 fs-5">{{ $payroll->payroll_name }}</p>
                </th>
            </tr>
        </thead>
    </table>
    <table class="table table-borderless border-dark border border-2 m-0">
        <thead>
            
            <tr>
                <td class="p-1" style="width:150px">Salary Slip of</td>
                <td class="p-1">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                <td class="p-1" style="width:150px">Employee Code</td>
                <td class="p-1">{{ $emp->employee->employee_code }}</td>
            </tr>
            <tr>
                <td class="p-1" >Designation</td>
                <td class="p-1">{{ $emp->employee->employee_designation->designation->designation }}</td>
                <td class="p-1">Loss of Pays</td>
                <td class="p-1">{{ $emp->payroll_employee_attendances->lop }}</td>
            </tr>
            <tr>
                <td class="p-1">Bank A/C No</td>
                <td class="p-1" colspan="3">
                    @if($emp->employee->employee_bank)
                    {{ $emp->employee->employee_bank->bank_name }} - 
                    {{ $emp->employee->employee_bank->account_number }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="p-1" >EPF A/C</td>
                <td class="p-1">{{ $emp->employee->pf }}</td>
                <td class="p-1">Date of Joining</td>
                <td class="p-1">{{ date('d-m-Y', strtotime($emp->employee->doj)) }}</td>
            </tr>
        </thead>
    </table>

    <table class="table table-bordered border border-dark border-3 m-0 mb-5">
        <thead class="text-center">
            <tr>
                <th class="w-50">Earning</th>
                <th>Deduction</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <table class="table table-borderless">
                        @foreach($emp->payroll_employee_earnings as $breakup)
                        <tr>
                            <td class="p-1">{{ $breakup->name_in_payslip }}</td>
                            <td class="p-1 text-end">{{ $breakup->actual_payable_amount }}</td>
                        </tr>
                        @endforeach
                    </table>
                </td>
                <td>
                    <table class="table table-borderless">
                        @foreach($emp->payroll_employee_deductions as $breakup)
                        <tr>
                            <td class="p-1">{{ $breakup->name_in_payslip }}</td>
                            <td class="p-1 text-end">{{ $breakup->actual_payable_amount }}</td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr class="text-end">
                <td>Gross Salary: {{ $emp->gross_salary }}</td>
                <td>Gross Deduction: {{ $emp->gross_deduction }}</td>
            </tr>
            <tr class="text-end">
                <td colspan="2">
                    <h6 class="m-0 fw-bold mb-2">Net Payable Amount: &#8377; {{ $emp->net_payable_amount }}/-</h6>
                    <h6 class="m-0 fw-bold text-capitalize">{{ $emp->amount_str }}</h6>
                </td>
            </tr>
        </tbody>
    </table>
    @endforeach


</div>

@endsection