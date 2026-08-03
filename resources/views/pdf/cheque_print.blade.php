<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cheque Print</title>
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
            color: #000000;
        }
        .cheque-leaf {
            width: 575.43pt;
            height: 235pt;
            position: relative;
            box-sizing: border-box;
            background: transparent;
            page-break-inside: avoid;
            overflow: hidden;
        }

        /* A/C PAYEE ONLY CROSSING STAMP */
        .ac-payee-stamp {
            position: absolute;
            top: 8mm;
            left: 0mm;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 1px;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            padding: 1mm 2.5mm;
            text-transform: uppercase;
            transform: rotate(-45deg);
            color: #000000;
        }

        /* DATE DIGITS GRID (Pre-printed box alignment: 8 digits spaced) */
        .cheque-date-grid {
            position: absolute;
            top: 10.5mm;
            left: 147mm;
            height: 6mm;
            line-height: 6mm;
        }
        .date-digit {
            display: inline-block;
            width: 5.4mm;
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            font-family: 'Courier', monospace, sans-serif;
            color: #000000;
        }

        /* PAYEE NAME (Pay line alignment) */
        .payee-name {
            position: absolute;
            top: 18mm;
            left: 28mm;
            width: 145mm;
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.2;
            letter-spacing: 0.3px;
        }

        /* AMOUNT IN WORDS (Rupees line alignment) */
        .rupees-words {
            position: absolute;
            top: 26.5mm;
            left: 32mm;
            width: 140mm;
            font-size: 10.5pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.4;
            letter-spacing: 0.2px;
        }

        /* AMOUNT IN FIGURES (₹ Box alignment) */
        .amount-figures {
            position: absolute;
            top: 48mm;
            left: 148mm;
            width: 48mm;
            font-size: 11.5pt;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.5px;
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

            <!-- 1. A/C PAYEE ONLY CROSSING STAMP -->
            <div class="ac-payee-stamp">A/C PAYEE ONLY</div>

            <!-- 2. DATE DIGITS GRID (Aligned with pre-printed DDMMYYYY boxes) -->
            <div class="cheque-date-grid">
                <span class="date-digit">{{ $d1 }}</span><span class="date-digit">{{ $d2 }}</span><span class="date-digit">{{ $m1 }}</span><span class="date-digit">{{ $m2 }}</span><span class="date-digit">{{ $y1 }}</span><span class="date-digit">{{ $y2 }}</span><span class="date-digit">{{ $y3 }}</span><span class="date-digit">{{ $y4 }}</span>
            </div>

            <!-- 3. PAYEE NAME (Aligned with pre-printed 'Pay' line) -->
            <div class="payee-name">
                {{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}
            </div>

            <!-- 4. AMOUNT IN WORDS (Aligned with pre-printed 'Rupees' line) -->
            <div class="rupees-words">
                *** {{ $emp->amount_str }} Rupees Only ***
            </div>

            <!-- 5. AMOUNT IN FIGURES (Aligned inside pre-printed '₹' box) -->
            <div class="amount-figures">
                *** {{ number_format($emp->net_payable_amount, 2) }} /-
            </div>

        </div>
    @endforeach

</body>
</html>
