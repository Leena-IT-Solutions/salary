@extends('layouts.newapp')

@section('head')
    <title>Payslip for {{ $payroll->payroll_name }}</title>
@endsection

@section('content')

    <payroll-details
        :payroll="{{ $payroll->load('payroll_employees.employee.employee_designation.designation', 'payroll_employees.employee.employee_bank', 'payroll_employees.payroll_employee_attendances', 'payroll_employees.payroll_employee_breakups') }}"
        :company="{{ $company }}">
    </payroll-details>

@endsection