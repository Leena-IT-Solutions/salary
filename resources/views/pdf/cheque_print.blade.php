<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cheque Printing - {{ $payroll->payroll_name }}</title>
    <style>
        @page {
            margin: 0px;
            size: 575.43pt 263.62pt landscape;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #ffffff;
            color: #0f172a;
            width: 203mm;
            height: 93mm;
            margin: 0;
            padding: 0;
        }
        .cheque-leaf {
            width: 203mm;
            height: 93mm;
            padding: 6mm 8mm;
            position: relative;
            box-sizing: border-box;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            page-break-after: always;
        }
        .cheque-leaf:last-child {
            page-break-after: avoid;
        }
        .ac-payee-stamp {
            position: absolute;
            top: 7mm;
            left: 10mm;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding: 1mm 2.5mm;
            text-transform: uppercase;
            transform: rotate(-6deg);
            color: #0f172a;
        }
        .cheque-date-box {
            position: absolute;
            top: 6mm;
            right: 8mm;
        }
        .date-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #475569;
            margin-right: 1.5mm;
        }
        .date-cell {
            display: inline-block;
            width: 4.8mm;
            height: 5.8mm;
            border: 1px solid #334155;
            text-align: center;
            line-height: 5.8mm;
            font-size: 8.5pt;
            font-weight: bold;
            background: #ffffff;
            margin-left: 0.4mm;
        }
        .payee-section {
            position: absolute;
            top: 22mm;
            left: 10mm;
            right: 8mm;
        }
        .label-text {
            font-size: 8pt;
            font-weight: bold;
            color: #475569;
            display: inline-block;
            width: 14mm;
        }
        .payee-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px dotted #94a3b8;
            padding-bottom: 1mm;
            display: inline-block;
            width: 165mm;
        }
        .rupees-section {
            position: absolute;
            top: 34mm;
            left: 10mm;
            right: 8mm;
        }
        .rupees-words {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.4;
            border-bottom: 1px dotted #94a3b8;
            padding-bottom: 1mm;
            display: inline-block;
            width: 161mm;
        }
        .amount-figures-box {
            position: absolute;
            top: 55mm;
            right: 8mm;
            width: 52mm;
            height: 9.5mm;
            border: 1.5px solid #1e293b;
            background: #ffffff;
            text-align: right;
            line-height: 9.5mm;
            padding-right: 3mm;
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .signature-block {
            position: absolute;
            bottom: 6mm;
            right: 8mm;
            text-align: right;
        }
        .company-title {
            font-size: 8pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10mm;
        }
        .sig-line {
            border-top: 1px solid #475569;
            font-size: 7.5pt;
            font-weight: bold;
            color: #334155;
            padding-top: 1mm;
            width: 48mm;
            display: inline-block;
        }
        .cheque-ref {
            position: absolute;
            bottom: 6mm;
            left: 10mm;
            font-size: 7pt;
            color: #64748b;
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
                <strong>Payroll Ref:</strong> {{ $payroll->payroll_name }}<br>
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
