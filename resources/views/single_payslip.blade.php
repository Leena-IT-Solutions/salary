@extends('layouts.newapp')

@section('head')
    <title>Salary Slip - {{ $emp->employee->first_name }}</title>
    <style>
        .payslip-container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 2rem auto;
        }
        .header-bg { background: #f8fafc; border-radius: 0.5rem; margin-bottom: 2rem; }
        .section-title { 
            background: #eef2ff; 
            color: #4338ca; 
            font-weight: 800; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            padding: 0.5rem 1rem; 
            border-radius: 0.25rem;
            margin: 1.5rem 0 1rem 0;
        }
        .net-payable-box {
            background: #4338ca;
            color: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-top: 2rem;
        }
        @media print {
            .no-print { display: none; }
            .payslip-container { box-shadow: none; margin: 0; padding: 0; }
        }
    </style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="javascript:history.back()" class="btn btn-light rounded-pill px-4 shadow-sm border">
            <i class="bi bi-arrow-left me-2"></i> Back to Profile
        </a>
        <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-printer me-2"></i> Print Slip
        </button>
    </div>

    <div class="payslip-container">
        <div class="header-bg p-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="fw-900 text-primary mb-1">{{ $company->company_name }}</h2>
                    <p class="text-muted small mb-0">{{ $company->address }}, {{ $company->city }}</p>
                </div>
                <div class="col-auto text-end">
                    <h1 class="fw-900 text-muted opacity-25 mb-0" style="font-size: 2.5rem;">PAYSLIP</h1>
                    <p class="fw-bold text-dark mb-0">{{ $payroll->payroll_name }}</p>
                </div>
            </div>
        </div>

        <div class="section-title">Employee Information</div>
        <div class="row g-4">
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted w-50">Employee Name</td><td class="fw-bold">{{ $emp->employee->first_name }} {{ $emp->employee->last_name }}</td></tr>
                    <tr><td class="text-muted">Employee Code</td><td class="fw-bold">{{ $emp->employee->employee_code }}</td></tr>
                    <tr><td class="text-muted">Designation</td><td class="fw-bold">{{ $emp->employee->employee_designation->designation->designation ?? '—' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted w-50">Date of Joining</td><td class="fw-bold">{{ date('d M Y', strtotime($emp->employee->doj)) }}</td></tr>
                    <tr><td class="text-muted">Bank A/C No</td><td class="fw-bold">{{ $emp->employee->employee_bank->account_number ?? '—' }}</td></tr>
                    <tr><td class="text-muted">PF Account</td><td class="fw-bold">{{ $emp->employee->pf ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="section-title">Attendance Summary</div>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="bg-light">
                    <tr><th>Working Days</th><th>Loss of Pay (LOP)</th><th>Payable Days</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $payroll->working_days }}</td>
                        <td class="text-danger fw-bold">{{ $emp->payroll_employee_attendances->lop ?? 0 }}</td>
                        <td class="fw-bold text-success">{{ $emp->payroll_employee_attendances->payable_days ?? $payroll->working_days }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 border-end">
                <div class="section-title mt-0">Earnings Breakdown</div>
                <table class="table table-sm table-borderless">
                    @foreach($emp->payroll_employee_earnings as $breakup)
                    <tr>
                        <td class="text-muted">{{ $breakup->name_in_payslip }}</td>
                        <td class="text-end fw-bold">₹ {{ number_format($breakup->actual_payable_amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top">
                        <td class="fw-bold">Gross Earnings</td>
                        <td class="text-end fw-900 text-primary">₹ {{ number_format($emp->gross_salary, 2) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="section-title mt-0">Deductions Breakdown</div>
                <table class="table table-sm table-borderless">
                    @foreach($emp->payroll_employee_deductions as $breakup)
                    <tr>
                        <td class="text-muted">{{ $breakup->name_in_payslip }}</td>
                        <td class="text-end fw-bold">₹ {{ number_format($breakup->actual_payable_amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top">
                        <td class="fw-bold">Total Deductions</td>
                        <td class="text-end fw-900 text-danger">₹ {{ number_format($emp->gross_deduction, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="net-payable-box">
            <div class="row align-items-center">
                <div class="col">
                    <div class="small text-uppercase opacity-75 fw-bold">Net Final Payout</div>
                    <div class="h2 fw-900 mb-0">₹ {{ number_format($emp->net_payable_amount, 2) }}</div>
                </div>
                <div class="col-auto text-end">
                    <div class="small italic opacity-75">"{{ $emp->amount_str }} Rupees Only"</div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center text-muted tiny">
            <p>This is a digitally generated payslip. No physical signature is required.</p>
        </div>
    </div>
</div>
@endsection
