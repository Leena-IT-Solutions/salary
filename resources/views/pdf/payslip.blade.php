@extends('layouts.pdf')

@section('head')
    <title>Payslips - {{ $payroll->payroll_name }}</title>
@endsection

@section('content')

    @foreach($payroll->payroll_employees as $ind => $emp)
        <div class="{{ ($ind + 1) == sizeof($payroll->payroll_employees) ? '' : 'page-break' }}">
            
            <div class="header-bg" style="padding: 5mm 12mm;">
                <table class="table-borderless">
                    <tr>
                        <td class="w-half">
                            <h2 class="text-primary fw-bold text-uppercase" style="font-size: 16pt;">{{ $company->company_name }}</h2>
                            <p class="text-muted small">{{ $company->address }}, {{ $company->city }}</p>
                        </td>
                        <td class="w-half text-end">
                            <h1 class="fw-bold text-uppercase m-0" style="font-size: 18pt; color: #cbd5e1;">PAYSLIP</h1>
                            <p class="fw-bold m-0" style="font-size: 9pt;">{{ $payroll->payroll_name }}</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="padding-top: 4mm; padding-bottom: 4mm;">
                
                <div class="section-title" style="padding: 1mm 2mm; font-size: 8pt; margin-bottom: 1.5mm;">Employee Information</div>
                <table class="table-borderless" style="margin-bottom: 2mm;">
                    <tr>
                        <td class="w-half">
                            <table class="table-borderless" style="padding: 0">
                                <tr>
                                    <td class="text-muted" style="width: 40%; padding: 0.5mm 0;">Employee Name:</td>
                                    <td class="fw-bold" style="padding: 0.5mm 0;">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding: 0.5mm 0;">Employee Code:</td>
                                    <td class="fw-bold" style="padding: 0.5mm 0;">{{ $emp->employee->employee_code }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding: 0.5mm 0;">Designation:</td>
                                    <td class="fw-bold" style="padding: 0.5mm 0;">{{ $emp->employee->employee_designation->designation->designation }}</td>
                                </tr>
                            </table>
                        </td>
                        <td class="w-half">
                            <table class="table-borderless" style="padding: 0">
                                <tr>
                                    <td class="text-muted" style="width: 40%; padding: 0.5mm 0;">Date of Joining:</td>
                                    <td class="fw-bold" style="padding: 0.5mm 0;">{{ date('d M Y', strtotime($emp->employee->doj)) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding: 0.5mm 0;">Bank A/C No:</td>
                                    <td class="fw-bold" style="padding: 0.5mm 0;">
                                        @if($emp->employee->employee_bank)
                                            {{ $emp->employee->employee_bank->account_number }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding: 0.5mm 0;">EPF A/C:</td>
                                    <td class="fw-bold" style="padding: 0.5mm 0;">{{ $emp->employee->pf ?? '--' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="section-title" style="padding: 1mm 2mm; font-size: 8pt; margin-bottom: 1.5mm;">Attendance Details</div>
                <table class="table-bordered text-center" style="margin-bottom: 3mm;">
                    <thead>
                        <tr style="background-color: #f8fafc;">
                            <th style="padding: 1mm;">Working Days</th>
                            <th style="padding: 1mm;">Loss of Pay (LOP)</th>
                            <th style="padding: 1mm;">Payable Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 1mm;">{{ $payroll->working_days }}</td>
                            <td style="padding: 1mm;" class="text-danger">{{ $emp->payroll_employee_attendances->lop }}</td>
                            <td style="padding: 1mm;" class="fw-bold">{{ $emp->payroll_employee_attendances->payable_days }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="section-title" style="padding: 1mm 2mm; font-size: 8pt; margin-bottom: 1.5mm;">Earnings & Deductions</div>
                <table class="table-borderless" style="margin-bottom: 2mm;">
                    <tr>
                        <td class="w-half" style="border-right: 1px solid #e2e8f0; padding-right: 3mm; padding-top: 0;">
                            <table class="table-borderless">
                                <thead>
                                    <tr class="fw-bold"><td style="padding: 0.5mm 0;">Earnings</td><td class="text-end" style="padding: 0.5mm 0;">Amount</td></tr>
                                </thead>
                                <tbody>
                                    @foreach($emp->payroll_employee_earnings as $breakup)
                                    <tr>
                                        <td class="text-muted" style="padding: 0.5mm 0;">{{ $breakup->name_in_payslip }}</td>
                                        <td class="text-end" style="padding: 0.5mm 0;">Rs. {{ number_format($breakup->actual_payable_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="fw-bold" style="border-top: 1px solid #e2e8f0;">
                                        <td style="padding: 1mm 0 0.5mm 0;">Gross Salary</td>
                                        <td class="text-end" style="padding: 1mm 0 0.5mm 0;">Rs. {{ number_format($emp->gross_salary, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="w-half" style="padding-left: 3mm; padding-top: 0;">
                            <table class="table-borderless">
                                <thead>
                                    <tr class="fw-bold"><td style="padding: 0.5mm 0;">Deductions</td><td class="text-end" style="padding: 0.5mm 0;">Amount</td></tr>
                                </thead>
                                <tbody>
                                    @foreach($emp->payroll_employee_deductions as $breakup)
                                    <tr>
                                        <td class="text-muted" style="padding: 0.5mm 0;">{{ $breakup->name_in_payslip }}</td>
                                        <td class="text-end" style="padding: 0.5mm 0;">Rs. {{ number_format($breakup->actual_payable_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="fw-bold" style="border-top: 1px solid #e2e8f0;">
                                        <td style="padding: 1mm 0 0.5mm 0;">Total Deductions</td>
                                        <td class="text-end" style="padding: 1mm 0 0.5mm 0;">Rs. {{ number_format($emp->gross_deduction, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="net-payable-box" style="padding: 2.5mm; margin-top: 2mm;">
                    <table class="table-borderless">
                        <tr>
                            <td>
                                <div class="text-uppercase text-muted fw-bold" style="font-size: 7pt;">Net Payable Amount</div>
                                <div class="fw-bold text-primary" style="font-size: 14pt; margin-top: 0.5mm;">Rs. {{ number_format($emp->net_payable_amount, 2) }}</div>
                            </td>
                            <td class="text-end" style="vertical-align: middle;">
                                <div class="text-capitalize fw-bold italic" style="font-size: 8pt;">"{{ $emp->amount_str }} Rupees Only"</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top: 8mm;">
                    <table class="table-borderless">
                        <tr>
                            <td class="text-center" style="width: 33%">
                                <div style="height: 10mm; border-bottom: 1px solid #cbd5e1; width: 80%; margin: 0 auto;"></div>
                                <p style="font-size: 7pt; padding-top: 1mm;">Employer Signature</p>
                            </td>
                            <td class="text-center" style="width: 33%"></td>
                            <td class="text-center" style="width: 33%">
                                <div style="height: 10mm; border-bottom: 1px solid #cbd5e1; width: 80%; margin: 0 auto;"></div>
                                <p style="font-size: 7pt; padding-top: 1mm;">Employee Signature</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="text-center text-muted" style="margin-top: 5mm; font-size: 7pt;">This is a computer generated payslip and does not require a physical signature.</p>

            </div>

        </div>
    @endforeach

@endsection