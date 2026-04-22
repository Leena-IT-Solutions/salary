@extends('layouts.pdf')

@section('head')
    <title>Bank Letter - {{ $payroll->payroll_name }}</title>
@endsection

@section('content')

    <div class="header-bg">
        <table class="table-borderless">
            <tr>
                <td class="w-half">
                    <h2 class="text-primary fw-bold text-uppercase">{{ $company->company_name }}</h2>
                    <p class="text-muted small">
                        {{ $company->address }}<br>
                        {{ $company->city }}, {{ $company->state }} {{ $company->pincode }}<br>
                        Email: {{ $company->email }} | Contact: {{ $company->phone }}
                    </p>
                </td>
                <td class="w-half text-end">
                    <h1 class="fw-bold text-uppercase m-0" style="font-size: 20pt; color: #cbd5e1;">BANK ADVICE</h1>
                    <p class="fw-bold m-0">Ref: BL/{{ $payroll->id }}/{{ date('Y') }}</p>
                    <p class="text-muted m-0">Date: {{ date('d M Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="container">
        
        <div style="margin-bottom: 8mm;">
            <p class="fw-bold">To,</p>
            <p>The Branch Manager,</p>
            <p class="fw-bold">Karnataka Bank,</p>
            <p>Ambernath West Branch.</p>
        </div>

        <div style="margin-bottom: 6mm; border-left: 3px solid #6366f1; padding-left: 4mm;">
            <p><span class="fw-bold text-uppercase">Subject:</span> Salary Disbursement Request for <span class="fw-bold">{{ $payroll->payroll_name }}</span></p>
        </div>

        <div style="margin-bottom: 6mm;">
            <p>Dear Sir/Madam,</p>
            <p style="margin-top: 2mm;">Please find below the salary distribution details for our employees. We request you to kindly debit our account and credit the respective employee accounts as per the list provided at the earliest.</p>
        </div>

        <div class="section-title">Employee Payout Schedule</div>
        <table class="table-bordered text-center">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="width: 50px;">SR</th>
                    <th class="text-start">Employee Name</th>
                    <th style="width: 150px;">Bank Account No</th>
                    <th class="text-end" style="width: 140px;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payroll->payroll_employees as $ind => $emp)
                    <tr>
                        <td class="text-muted">{{ $ind + 1 }}</td>
                        <td class="text-start fw-bold">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                        <td>{{ $emp->employee->employee_bank->account_number ?? 'N/A' }}</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($emp->net_payable_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f5f3ff;">
                    <td colspan="3" class="text-end fw-bold text-primary text-uppercase" style="padding: 3mm;">Total Disbursement Amount</td>
                    <td class="text-end fw-bold text-primary" style="padding: 3mm; font-size: 11pt;">Rs. {{ number_format($payroll->net_payable_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 10mm; margin-bottom: 10mm;">
            <p class="fw-bold">Total Amount in Words:</p>
            <p class="text-capitalize italic p-2" style="background: #f8fafc; border: 1px dashed #cbd5e1;">"{{ $payroll->amount_str }} Rupees Only"</p>
        </div>

        <div style="margin-top: 15mm;">
            <p>Thanking you,</p>
            <p>Yours faithfully,</p>
            <p class="fw-bold" style="margin-top: 2mm;">For {{ $company->company_name }}</p>
            
            <table class="table-borderless" style="margin-top: 10mm;">
                <tr>
                    <td style="width: 40%">
                        <div style="height: 15mm; border-bottom: 1px solid #cbd5e1; width: 100%;"></div>
                        <p class="fw-bold pt-1">Authorized Signatory</p>
                        <p class="small text-muted">Stamp & Designation</p>
                    </td>
                    <td></td>
                </tr>
            </table>
        </div>

    </div>

@endsection