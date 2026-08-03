@extends('layouts.pdf')

@section('head')
    <title>Cheque Printing - {{ $payroll->payroll_name }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
        .page-container {
            width: 297mm;
            height: 190mm;
            padding: 4mm 0;
            page-break-after: always;
            box-sizing: border-box;
            overflow: hidden;
        }
        .page-container:last-child {
            page-break-after: avoid;
        }
        
        /* CTS-2010 Standard Cheque Leaf (203mm x 93mm) */
        .cheque-leaf {
            width: 203mm;
            height: 93mm;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #1e293b;
            border-radius: 4px;
            position: relative;
            box-sizing: border-box;
            padding: 6mm 8mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Subtle Security Watermark Background */
        .cheque-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.03;
            background-image: repeating-linear-gradient(45deg, #000 0, #000 1px, transparent 0, transparent 50%);
            background-size: 10px 10px;
            pointer-events: none;
        }

        /* A/C PAYEE STAMP */
        .ac-payee-stamp {
            position: absolute;
            top: 6mm;
            left: 10mm;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding: 1mm 3mm;
            text-transform: uppercase;
            transform: rotate(-6deg);
            color: #0f172a;
        }

        /* BANK / COMPANY HEADER */
        .cheque-header {
            position: absolute;
            top: 6mm;
            left: 45mm;
        }
        .bank-name {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .bank-sub {
            font-size: 6.5pt;
            color: #64748b;
            text-transform: uppercase;
        }

        /* DATE GRID BOX (DDMMYYYY) */
        .cheque-date-box {
            position: absolute;
            top: 6mm;
            right: 8mm;
        }
        .date-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #334155;
            margin-right: 2mm;
        }
        .date-cell {
            display: inline-block;
            width: 4.5mm;
            height: 5.5mm;
            border: 1px solid #334155;
            text-align: center;
            line-height: 5.5mm;
            font-size: 8.5pt;
            font-weight: bold;
            background: #ffffff;
            margin-left: 0.5mm;
            color: #0f172a;
        }

        /* PAYEE SECTION */
        .payee-container {
            position: absolute;
            top: 22mm;
            left: 10mm;
            right: 8mm;
        }
        .payee-label {
            font-size: 8.5pt;
            font-weight: bold;
            color: #334155;
            display: inline-block;
            width: 12mm;
        }
        .payee-val {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px dotted #64748b;
            display: inline-block;
            width: 145mm;
            padding-bottom: 0.5mm;
        }
        .or-order {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748b;
            display: inline-block;
            width: 20mm;
            text-align: right;
        }

        /* RUPEES SECTION */
        .rupees-container {
            position: absolute;
            top: 33mm;
            left: 10mm;
            right: 8mm;
        }
        .rupees-label {
            font-size: 8.5pt;
            font-weight: bold;
            color: #334155;
            display: inline-block;
            width: 16mm;
        }
        .rupees-val {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px dotted #64748b;
            display: inline-block;
            width: 162mm;
            padding-bottom: 0.5mm;
            line-height: 1.3;
        }

        /* AMOUNT IN FIGURES BOX */
        .amount-box {
            position: absolute;
            top: 52mm;
            right: 8mm;
            width: 52mm;
            height: 9mm;
            border: 1.5px solid #0f172a;
            background: #ffffff;
            text-align: right;
            line-height: 9mm;
            padding-right: 3mm;
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        /* SIGNATURE BLOCK */
        .signature-container {
            position: absolute;
            bottom: 13mm;
            right: 8mm;
            text-align: right;
        }
        .comp-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8mm;
        }
        .sig-text {
            border-top: 1px solid #475569;
            font-size: 7pt;
            font-weight: bold;
            color: #334155;
            padding-top: 1mm;
            width: 45mm;
            display: inline-block;
        }

        /* FOOTER DETAILS & MICR BAND */
        .cheque-footer-info {
            position: absolute;
            bottom: 13mm;
            left: 10mm;
            font-size: 6.5pt;
            color: #64748b;
            line-height: 1.3;
        }

        /* CTS-2010 MICR CODE BAND */
        .micr-band {
            position: absolute;
            bottom: 3mm;
            left: 0;
            right: 0;
            text-align: center;
            font-family: 'Courier', monospace;
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 2px;
        }

        /* ADVICE SHEET BELOW CHEQUE */
        .advice-sheet {
            width: 203mm;
            margin: 6mm auto 0 auto;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 4mm 6mm;
            box-sizing: border-box;
        }
        .advice-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1.5mm;
            margin-bottom: 2mm;
        }
    </style>
@endsection

@section('content')

    <?php
        $d1 = date('d')[0]; $d2 = date('d')[1];
        $m1 = date('m')[0]; $m2 = date('m')[1];
        $y1 = date('Y')[0]; $y2 = date('Y')[1]; $y3 = date('Y')[2]; $y4 = date('Y')[3];
    ?>

    @foreach($payroll->payroll_employees as $ind => $emp)
        <div class="page-container">
            
            <!-- CTS-2010 CHEQUE LEAF -->
            <div class="cheque-leaf">
                <div class="cheque-bg-pattern"></div>

                <!-- A/C PAYEE ONLY CROSSING STAMP -->
                <div class="ac-payee-stamp">A/C PAYEE ONLY</div>

                <!-- BANK / COMPANY HEADER -->
                <div class="cheque-header">
                    <div class="bank-name">{{ $company->company_name }}</div>
                    <div class="bank-sub">Salary Disbursement Account • CTS-2010 Standard</div>
                </div>

                <!-- DATE GRID BOX (DDMMYYYY) -->
                <div class="cheque-date-box">
                    <span class="date-label">DATE:</span>
                    <span class="date-cell">{{ $d1 }}</span><span class="date-cell">{{ $d2 }}</span>
                    <span class="date-cell">{{ $m1 }}</span><span class="date-cell">{{ $m2 }}</span>
                    <span class="date-cell">{{ $y1 }}</span><span class="date-cell">{{ $y2 }}</span><span class="date-cell">{{ $y3 }}</span><span class="date-cell">{{ $y4 }}</span>
                </div>

                <!-- PAYEE LINE -->
                <div class="payee-container">
                    <span class="payee-label">PAY</span>
                    <span class="payee-val">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</span>
                    <span class="or-order">OR ORDER</span>
                </div>

                <!-- RUPEES IN WORDS LINE -->
                <div class="rupees-container">
                    <span class="rupees-label">RUPEES</span>
                    <span class="rupees-val">{{ $emp->amount_str }} Rupees Only</span>
                </div>

                <!-- AMOUNT IN FIGURES BOX -->
                <div class="amount-box">
                    Rs. *** {{ number_format($emp->net_payable_amount, 2) }} /-
                </div>

                <!-- FOOTER REFERENCE INFORMATION -->
                <div class="cheque-footer-info">
                    <strong>Payroll:</strong> {{ $payroll->payroll_name }}<br>
                    <strong>Emp Code:</strong> {{ $emp->employee->employee_code }} | <strong>Bank Acc:</strong> {{ $emp->employee->employee_bank->account_number ?? 'N/A' }}
                </div>

                <!-- SIGNATURE BLOCK -->
                <div class="signature-container">
                    <div class="comp-title">For {{ $company->company_name }}</div>
                    <div class="sig-text">Authorized Signatory</div>
                </div>

                <!-- CTS-2010 MICR CODE BAND -->
                <div class="micr-band">
                    c {{ str_pad($ind + 1, 6, '0', STR_PAD_LEFT) }} c   400024012 c   {{ str_pad($emp->id, 6, '0', STR_PAD_LEFT) }} c  31
                </div>
            </div>

            <!-- PAYMENT ADVICE SLIP BELOW CHEQUE -->
            <div class="advice-sheet">
                <div class="advice-title">Salary Payment Advice Slip</div>
                <table class="table-borderless small w-full">
                    <tr>
                        <td style="width: 50%;">
                            <strong>Employee Name:</strong> {{ $emp->employee->first_name }} {{ $emp->employee->last_name }}<br>
                            <strong>Employee Code:</strong> {{ $emp->employee->employee_code }}<br>
                            <strong>Designation:</strong> {{ $emp->employee->employee_designation->designation->designation ?? 'N/A' }}
                        </td>
                        <td style="width: 50%; text-align: right;">
                            <strong>Payroll Period:</strong> {{ $payroll->payroll_name }}<br>
                            <strong>Bank Account:</strong> {{ $emp->employee->employee_bank->bank_name ?? 'N/A' }} - {{ $emp->employee->employee_bank->account_number ?? 'N/A' }}<br>
                            <strong>Net Salary Payable:</strong> <span class="text-primary fw-bold">Rs. {{ number_format($emp->net_payable_amount, 2) }}</span>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    @endforeach

@endsection
