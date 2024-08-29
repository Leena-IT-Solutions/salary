@extends('layouts.pdf')

@section('head')
    <title>Bank Letter</title>
@endsection

@section('content')

<div class="p-5">

    <div class="text-center pb-2">
        <h1 class="m-0 text-uppercase fw-bold">{{ $company->company_name }}</h1>
        <h5 class="text-uppercase">M. L. Shah Charitable Trust</h5>
        <h5>Govt. Regd. No. SFS 1017/P.No.574/SM-2</h5>
        <h5>U.DISE CODE: 27211508907</h5>
        <h5 class="fs-6 m-0 text-center">{{ $company->address }} {{ $company->city }} {{ $company->pincode }} {{ $company->state }} {{ $company->country }}</h5>
    </div>
    
    <table class="table-top-bottom pb-2">
        <tr>
            <td>Email Address: {{ $company->email }}</td>
            <td class="text-end">Contact Number: {{ $company->phone }}</td>
        </tr>
    </table>

    <table class="table-borderless pb-2">
        <tr>
            <td>Ref No.: {{ $payroll->id }}</td>
            <td class="text-end">Date: {{date('d-m-Y')}}</td>
        </tr>
    </table>

    <table class="table-borderless pb-2">
        <tr>
            <td>
                To, <br>
                The Manager <br>
                Karnataka Bank <br>
                Ambernath West
            </td>
        </tr>
    </table>

    <table class="table-borderless pb-2">
        <tr>
            <td class="fw-bold">
                Subject: Salary for payroll {{ $payroll->payroll_name }}
            </td>
        </tr>
    </table>

    <table class="table-borderless pb-2">
        <tr>
            <td>
                Dear Sir,
            </td>
        </tr>
        <tr>
            <td>
                Below is the salary list of our employees along with the account details, kindly release their salary at the earliest.
            </td>
        </tr>
    </table>

    <table class="pb-3">
        <thead class="text-center text-uppercase">
            <tr>
                <th style="width: 80px;" class="text-center">SR NO</th>
                <th>Name</th>
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
                <td class="text-end">Rs {{ $payroll->net_payable_amount }}/-</td>
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