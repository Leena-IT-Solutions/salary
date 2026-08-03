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

        /* A/C PAYEE ONLY CROSSING STAMP (Exact Canva Vector Coordinates) */
        .ac-payee-stamp {
            position: absolute;
            top: 8.8mm;
            left: -3.6mm;
            width: 25.1mm;
            height: 4.3mm;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            text-align: center;
            line-height: 4.3mm;
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            transform: rotate(-45deg);
            color: #000000;
        }

        /* DATE DIGITS GRID: Top 5.5mm, Left 149mm, Width 45mm (8 boxes, each 5.6mm) */
        .cheque-date-grid {
            position: absolute;
            top: 5.5mm;
            left: 149mm;
            width: 45mm;
            height: 6mm;
            line-height: 6mm;
        }
        .date-digit {
            display: inline-block;
            width: 5.6mm;
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            font-family: 'Courier', monospace, sans-serif;
            color: #000000;
        }

        /* PAYEE NAME: Pay line at Top 24mm, Left 18mm */
        .payee-name {
            position: absolute;
            top: 20.5mm;
            left: 18mm;
            width: 128mm;
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.2;
            letter-spacing: 0.3px;
        }

        /* AMOUNT IN WORDS: Rupees line at Top 33mm, Left 33mm */
        .rupees-words {
            position: absolute;
            top: 29.5mm;
            left: 33mm;
            width: 160mm;
            font-size: 10.5pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.3;
            letter-spacing: 0.2px;
        }

        /* AMOUNT IN FIGURES: ₹ Box at Top 33mm, Left 154mm (153mm + 1mm offset), Width 37mm, Height 8.5mm */
        .amount-figures {
            position: absolute;
            top: 33mm;
            left: 154mm;
            width: 37mm;
            height: 8.5mm;
            line-height: 8.5mm;
            font-size: 11pt;
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

            <!-- 2. DATE DIGITS GRID (Top: 5.5mm, Left: 149mm, Width: 45mm) -->
            <div class="cheque-date-grid">
                <span class="date-digit">{{ $d1 }}</span><span class="date-digit">{{ $d2 }}</span><span class="date-digit">{{ $m1 }}</span><span class="date-digit">{{ $m2 }}</span><span class="date-digit">{{ $y1 }}</span><span class="date-digit">{{ $y2 }}</span><span class="date-digit">{{ $y3 }}</span><span class="date-digit">{{ $y4 }}</span>
            </div>

            <!-- 3. PAYEE NAME (Top: 24mm line, Left: 18mm) -->
            <div class="payee-name">
                {{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}
            </div>

            <!-- 4. AMOUNT IN WORDS (Top: 33mm line, Left: 33mm) -->
            <div class="rupees-words">
                *** {{ $emp->amount_str }} Rupees Only ***
            </div>

            <!-- 5. AMOUNT IN FIGURES (Top: 33mm, Left: 154mm, Width: 38.5mm) -->
            <div class="amount-figures">
                *** {{ number_format($emp->net_payable_amount, 2) }} /-
            </div>

        </div>
    @endforeach

</body>
</html>
