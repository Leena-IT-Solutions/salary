@extends('layouts.pdf')

@section('head')
    <title>Cheque Printing - {{ $payroll->payroll_name }}</title>
    <style>
        .cheque-card {
            width: 100%;
            height: 92mm;
            border: 2px solid #334155;
            border-radius: 4px;
            background-color: #f8fafc;
            position: relative;
            padding: 5mm 6mm;
            margin-bottom: 6mm;
            box-sizing: border-box;
        }
        .ac-payee-stamp {
            position: absolute;
            top: 6mm;
            left: 8mm;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            border-top: 1.5px solid #1e293b;
            border-bottom: 1.5px solid #1e293b;
            padding: 1.5mm 3mm;
            text-transform: uppercase;
            transform: rotate(-5deg);
            color: #1e293b;
        }
        .cheque-date-box {
            position: absolute;
            top: 5mm;
            right: 6mm;
        }
        .date-cell {
            display: inline-block;
            width: 5mm;
            height: 6mm;
            border: 1px solid #475569;
            text-align: center;
            line-height: 6mm;
            font-size: 9pt;
            font-weight: bold;
            background: #fff;
            margin-left: 0.5mm;
        }
        .cheque-line {
            border-bottom: 1px dotted #64748b;
            font-weight: bold;
            font-size: 10pt;
            color: #0f172a;
            padding-bottom: 1mm;
        }
        .amount-box {
            position: absolute;
            bottom: 16mm;
            right: 6mm;
            width: 52mm;
            height: 9mm;
            border: 1.5px solid #1e293b;
            background: #ffffff;
            text-align: right;
            line-height: 9mm;
            padding-right: 3mm;
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
        }
        .signature-block {
            position: absolute;
            bottom: 5mm;
            right: 6mm;
            text-align: right;
        }
        .cheque-title {
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')

    <?php
        $todayStr = date('dmy'); // 6 digits: DDMMYY or 8 digits: DDMMYYYY
        $d1 = date('d')[0]; $d2 = date('d')[1];
        $m1 = date('m')[0]; $m2 = date('m')[1];
        $y1 = date('Y')[0]; $y2 = date('Y')[1]; $y3 = date('Y')[2]; $y4 = date('Y')[3];
    ?>

    <div class="header-bg" style="padding: 4mm 8mm;">
        <table class="table-borderless">
            <tr>
                <td class="w-half">
                    <h2 class="text-primary fw-bold text-uppercase" style="font-size: 13pt;">{{ $company->company_name }}</h2>
                    <p class="text-muted small m-0">Salary Payment Cheque Printing: <span class="fw-bold">{{ $payroll->payroll_name }}</span></p>
                </td>
                <td class="w-half text-end">
                    <h1 class="fw-bold text-uppercase m-0" style="font-size: 15pt; color: #cbd5e1;">CHEQUE PRINTING ADVICE</h1>
                    <p class="text-muted small m-0">Generated Date: {{ date('d M Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="container" style="padding: 5mm 8mm;">

        <!-- SECTION 1: CONSOLIDATED SALARY CHEQUE -->
        <div class="cheque-card">
            <!-- A/C PAYEE STAMP -->
            <div class="ac-payee-stamp">A/C PAYEE ONLY</div>

            <!-- DATE GRID -->
            <div class="cheque-date-box">
                <span class="cheque-title" style="margin-right: 2mm;">DATE:</span>
                <span class="date-cell">{{ $d1 }}</span><span class="date-cell">{{ $d2 }}</span>
                <span class="date-cell">{{ $m1 }}</span><span class="date-cell">{{ $m2 }}</span>
                <span class="date-cell">{{ $y1 }}</span><span class="date-cell">{{ $y2 }}</span><span class="date-cell">{{ $y3 }}</span><span class="date-cell">{{ $y4 }}</span>
            </div>

            <!-- PAYEE LINE -->
            <div style="margin-top: 18mm;">
                <table class="table-borderless">
                    <tr>
                        <td style="width: 12mm; vertical-align: bottom;" class="cheque-title">PAY:</td>
                        <td class="cheque-line" style="font-size: 11pt;">Yourself for Salary Disbursement</td>
                    </tr>
                </table>
            </div>

            <!-- RUPEES IN WORDS LINE -->
            <div style="margin-top: 4mm;">
                <table class="table-borderless">
                    <tr>
                        <td style="width: 18mm; vertical-align: bottom;" class="cheque-title">RUPEES:</td>
                        <td class="cheque-line" style="font-size: 10pt; line-height: 1.4;">
                            {{ $payroll->amount_str }} Rupees Only
                        </td>
                    </tr>
                </table>
            </div>

            <!-- AMOUNT IN FIGURES BOX -->
            <div class="amount-box">
                ₹ *** {{ number_format($payroll->net_payable_amount, 2) }} /-
            </div>

            <!-- SIGNATURE BLOCK -->
            <div class="signature-block">
                <p class="fw-bold" style="font-size: 8pt; margin-bottom: 8mm;">For {{ $company->company_name }}</p>
                <p class="fw-bold" style="font-size: 8pt; border-top: 1px solid #475569; padding-top: 1mm; width: 45mm; display: inline-block;">Authorized Signatory</p>
            </div>
        </div>

        <!-- SECTION 2: DISBURSEMENT SCHEDULE TABLE -->
        <div class="section-title" style="margin-bottom: 2mm;">Salary Disbursement Schedule</div>
        <table class="table-bordered text-center w-full mb-4">
            <thead>
                <tr style="background-color: #f1f5f9; font-size: 8pt;">
                    <th style="width: 35px;">SR</th>
                    <th style="width: 70px;">Emp Code</th>
                    <th class="text-start">Employee Name</th>
                    <th>Bank & Account No</th>
                    <th class="text-end" style="width: 100px;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payroll->payroll_employees as $ind => $emp)
                    <tr style="font-size: 8pt; {{ $ind % 2 == 0 ? '' : 'background-color: #fcfcfc;' }}">
                        <td class="text-muted">{{ $ind + 1 }}</td>
                        <td>{{ $emp->employee->employee_code }}</td>
                        <td class="text-start fw-bold">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                        <td>{{ $emp->employee->employee_bank->bank_name ?? 'N/A' }} - {{ $emp->employee->employee_bank->account_number ?? 'N/A' }}</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($emp->net_payable_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f5f3ff; font-size: 8.5pt;">
                    <td colspan="4" class="text-end fw-bold text-primary text-uppercase" style="padding: 2.5mm;">Total Salary Payable</td>
                    <td class="text-end fw-bold text-primary" style="padding: 2.5mm;">Rs. {{ number_format($payroll->net_payable_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- SUMMARY STATEMENT -->
        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 3mm 4mm; border-radius: 4px;">
            <p class="fw-bold small m-0">Net Payable Amount in Words:</p>
            <p class="text-capitalize italic fw-bold text-primary small m-0">"{{ $payroll->amount_str }} Rupees Only"</p>
        </div>

    </div>

@endsection
