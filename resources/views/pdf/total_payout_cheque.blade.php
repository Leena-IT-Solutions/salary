<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Total Net Payout Cheque</title>
    <?php
        $settingMap = isset($settings) && is_array($settings) ? $settings : \App\Models\Setting::pluck('value', 'key')->all();

        $getVal = function($k, $default) use ($settingMap) {
            return (isset($settingMap[$k]) && trim((string)$settingMap[$k]) !== '') ? trim((string)$settingMap[$k]) : $default;
        };

        $ac_payee_top = $getVal('Cheque Account Payee Top (mm)', '8.8');
        $ac_payee_left = $getVal('Cheque Account Payee Left (mm)', '1.4');

        $date_grid_top = $getVal('Cheque Date Grid Top (mm)', '9.25');
        $date_grid_left = $getVal('Cheque Date Grid Left (mm)', '149');

        $payee_name_top = $getVal('Cheque Employee Name Top (mm)', '23.3');
        $payee_name_left = $getVal('Cheque Employee Name Left (mm)', '18');

        $rupees_words_top = $getVal('Cheque Amount in Words Top (mm)', '31.5');
        $rupees_words_left = $getVal('Cheque Amount in Words Left (mm)', '33');

        $amount_figures_top = $getVal('Cheque Amount in Numbers Top (mm)', '34');
        $amount_figures_left = $getVal('Cheque Amount in Numbers Left (mm)', '154');

        $payee_text = $getVal('Total Payout Cheque Payee Name', 'Yourself for Salary');
    ?>
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
            height: 263.62pt;
            position: relative;
            box-sizing: border-box;
            background: transparent;
            page-break-inside: avoid;
            overflow: hidden;
        }

        /* A/C PAYEE ONLY CROSSING STAMP */
        .ac-payee-stamp {
            position: absolute;
            top: {{ $ac_payee_top }}mm;
            left: {{ $ac_payee_left }}mm;
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

        /* DATE DIGITS GRID */
        .cheque-date-grid {
            position: absolute;
            top: {{ $date_grid_top }}mm;
            left: {{ $date_grid_left }}mm;
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

        /* PAYEE NAME */
        .payee-name {
            position: absolute;
            top: {{ $payee_name_top }}mm;
            left: {{ $payee_name_left }}mm;
            width: 128mm;
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.2;
            letter-spacing: 0.3px;
        }

        /* AMOUNT IN WORDS */
        .rupees-words {
            position: absolute;
            top: {{ $rupees_words_top }}mm;
            left: {{ $rupees_words_left }}mm;
            width: 160mm;
            font-size: 10.5pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.3;
            letter-spacing: 0.2px;
        }

        /* AMOUNT IN FIGURES */
        .amount-figures {
            position: absolute;
            top: {{ $amount_figures_top }}mm;
            left: {{ $amount_figures_left }}mm;
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

    <div class="cheque-leaf">

        <!-- 1. A/C PAYEE ONLY CROSSING STAMP -->
        <div class="ac-payee-stamp">A/C PAYEE ONLY</div>

        <!-- 2. DATE DIGITS GRID -->
        <div class="cheque-date-grid">
            <span class="date-digit">{{ $d1 }}</span><span class="date-digit">{{ $d2 }}</span><span class="date-digit">{{ $m1 }}</span><span class="date-digit">{{ $m2 }}</span><span class="date-digit">{{ $y1 }}</span><span class="date-digit">{{ $y2 }}</span><span class="date-digit">{{ $y3 }}</span><span class="date-digit">{{ $y4 }}</span>
        </div>

        <!-- 3. PAYEE NAME (Total Net Payout Payee Text from Preferences) -->
        <div class="payee-name">
            {{ $payee_text }}
        </div>

        <!-- 4. AMOUNT IN WORDS -->
        <div class="rupees-words">
            *** {{ $payroll->amount_str }} Rupees Only ***
        </div>

        <!-- 5. AMOUNT IN FIGURES -->
        <div class="amount-figures">
            *** {{ number_format($payroll->net_payable_amount, 2) }} /-
        </div>

    </div>

</body>
</html>
