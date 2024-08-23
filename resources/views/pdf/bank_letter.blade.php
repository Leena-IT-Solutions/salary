@extends('layouts.pdf')

@section('head')
    <title>Bank Letter</title>
@endsection

@section('content')

<div class="p-5">

    <div class="container-fluid text-center mb-3">
        <h1 class="m-0 text-uppercase fw-bold text-primary">{{ $company->company_name }}</h1>
        <h5 class="text-uppercase">M. L. Shah Charitable Trust</h5>
        <h5>Govt. Regd. No. SFS 1017/P.No.574/SM-2</h5>
        <h5>U.DISE CODE: 27211508907</h5>
    </div>
    
    <div class="row g-1 border-top border-bottom border-primary border-2 py-2">
        <div class="col-12">
            <h5 class="fs-6 m-0 text-center">{{ $company->address }} {{ $company->city }} {{ $company->pincode }} {{ $company->state }} {{ $company->country }}</h5>
        </div>
        <div class="col">Email Address: {{ $company->email }}</div>
        <div class="col text-end">Contact Number: {{ $company->phone }}</div>
    </div>

    <div class="row g-1 py-3">
        <div class="col">Ref No.: {{ $payroll->id }}</div>
        <div class="col text-end">Date: {{date('d-m-Y')}}</div>
    </div>

    <div class="row g-3 py-2">
        <div class="col-12 text-capitalize fs-5">
            To, <br>
            The Manager <br>
            Karnataka Bank <br>
            Ambernath West
        </div>

        <div class="col text-capitalize fs-5 fw-bold">
            Subject: Salary for payroll {{ $payroll->payroll_name }}
        </div>
    </div>

    <div class="row g-2 py-2 mb-4">
        <div class="col-12 text-capitalize fs-5">
            Dear Sir,
        </div>
        <div class="col-12 text-capitalize fs-5">
            Below is the salary list of our employees along with the account details, kindly release their salary at the earliest.
        </div>
    </div>

    <table class="table table-bordered border-dark">
        <thead class="text-center text-uppercase">
            <tr>
                <th style="width: 80px;" class="text-center">SR NO</th>
                <th>name</th>
                <th>Account No</th>
                <th class="text-end" style="width: 200px;">Payable Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payroll->payroll_employees as $ind => $emp)
                <tr>
                    <td class="text-center">{{ $ind + 1 }}</td>
                    <td>{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                    <td class="text-center">
                        @if($emp->employee->employee_bank)
                            {{ $emp->employee->employee_bank->account_number }}
                        @endif
                    </td>
                    <td class="text-end">{{ $emp->net_payable_amount }}</td>
                </tr>
            @endforeach

            <tr class="fw-bold">
                <td class="text-end" colspan="3">Total Payable Amount</td>
                <td class="text-end">&#8377; {{ $payroll->net_payable_amount }}/-</td>
            </tr>

        </tbody>
    </table>

    <div class="row g-2 py-2 mb-4">
        <div class="col-12 text-capitalize fs-5">
            Thanking You,
            <br><br><br><br>
            <h4 class="fw-bold">Epsi Allwin Levi</h4>
            <h5 class="fw-bold">Principal</h5>
        </div>
    </div>

</div>

@endsection