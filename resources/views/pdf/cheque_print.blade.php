<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cheque Printing - {{ $payroll->payroll_name }}</title>
    <style>
        @page {
            margin: 0;
            size: 575.43pt 263.62pt;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 575.43pt;
            height: 263.62pt;
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #ffffff;
            color: #0f172a;
        }
        .cheque-leaf {
            width: 575.43pt;
            height: 235pt;
            padding: 12pt 20pt;
            position: relative;
            box-sizing: border-box;
            background: #ffffff;
            page-break-inside: avoid;
            overflow: hidden;
        }
        .ac-payee-stamp {
            position: absolute;
            top: 12pt;
            left: 20pt;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1px;
            border-top: 1.5pt solid #0f172a;
            border-bottom: 1.5pt solid #0f172a;
            padding: 2pt 5pt;
            text-transform: uppercase;
            transform: rotate(-5deg);
            color: #0f172a;
        }
        .cheque-date-box {
            position: absolute;
            top: 12pt;
            right: 20pt;
        }
        .date-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #475569;
            margin-right: 3pt;
        }
        .date-cell {
            display: inline-block;
            width: 12pt;
            height: 14pt;
            border: 1pt solid #334155;
            text-align: center;
            line-height: 14pt;
            font-size: 8.5pt;
            font-weight: bold;
            background: #ffffff;
            margin-left: 1pt;
        }
        .payee-section {
            position: absolute;
            top: 50pt;
            left: 20pt;
            right: 20pt;
        }
        .label-text {
            font-size: 8pt;
            font-weight: bold;
            color: #475569;
            display: inline-block;
            width: 40pt;
        }
        .payee-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1pt dotted #94a3b8;
            padding-bottom: 2pt;
            display: inline-block;
            width: 480pt;
        }
        .rupees-section {
            position: absolute;
            top: 82pt;
            left: 20pt;
            right: 20pt;
        }
        .rupees-words {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.4;
            border-bottom: 1pt dotted #94a3b8;
            padding-bottom: 2pt;
            display: inline-block;
            width: 465pt;
        }
        .amount-figures-box {
            position: absolute;
            top: 135pt;
            right: 20pt;
            width: 145pt;
            height: 24pt;
            border: 1.5pt solid #1e293b;
            background: #ffffff;
            text-align: right;
            line-height: 24pt;
            padding-right: 8pt;
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5pt;
        }
        .signature-block {
            position: absolute;
            bottom: 12pt;
            right: 20pt;
            text-align: right;
        }
        .company-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 22pt;
        }
        .sig-line {
            border-top: 1pt solid #475569;
            font-size: 7.5pt;
            font-weight: bold;
            color: #334155;
            padding-top: 2pt;
            width: 125pt;
            display: inline-block;
        }
        .cheque-ref {
            position: absolute;
            bottom: 12pt;
            left: 20pt;
            font-size: 7pt;
            color: #64748b;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    <?php
        $d1 = date('d')[0]; $d2 = date('d')[1];
        $m1 = date('m')[0]; $m2 = date('m')[1];
        $y1 = date('Y')[0]; $y2 = date('Y')[1]; $y3 = date('Y')[2]; $y4 = date('Y')[3];
    ?>

    @foreach($payroll->payroll_employees as $ind => $emp)
        <div class="cheque-leaf">
            <!-- A/C PAYEE ONLY CROSSING STAMP -->
            <div class="ac-payee-stamp">A/C PAYEE ONLY</div>

            <!-- DATE GRID BOX (DDMMYYYY) -->
            <div class="cheque-date-box">
                <span class="date-title">DATE:</span>
                <span class="date-cell">{{ $d1 }}</span><span class="date-cell">{{ $d2 }}</span>
                <span class="date-cell">{{ $m1 }}</span><span class="date-cell">{{ $m2 }}</span>
                <span class="date-cell">{{ $y1 }}</span><span class="date-cell">{{ $y2 }}</span><span class="date-cell">{{ $y3 }}</span><span class="date-cell">{{ $y4 }}</span>
            </div>

            <!-- PAYEE LINE -->
            <div class="payee-section">
                <span class="label-text">PAY</span>
                <span class="payee-name">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</span>
            </div>

            <!-- RUPEES IN WORDS LINE -->
            <div class="rupees-section">
                <span class="label-text">RUPEES</span>
                <span class="rupees-words">{{ $emp->amount_str }} Rupees Only</span>
            </div>

            <!-- AMOUNT IN FIGURES BOX -->
            <div class="amount-figures-box">
                Rs. *** {{ number_format($emp->net_payable_amount, 2) }} /-
            </div>

            <!-- REFERENCE & CHEQUE DETAILS -->
            <div class="cheque-ref">
                <strong>Payroll:</strong> {{ $payroll->payroll_name }}<br>
                <strong>Emp Code:</strong> {{ $emp->employee->employee_code }} | <strong>Bank Acc:</strong> {{ $emp->employee->employee_bank->account_number ?? 'N/A' }}
            </div>

            <!-- SIGNATURE BLOCK -->
            <div class="signature-block">
                <p class="company-title">For {{ $company->company_name }}</p>
                <p class="sig-line">Authorized Signatory</p>
            </div>
        </div>
    @endforeach

</body>
</html>
