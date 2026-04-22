@extends('layouts.pdf')

@section('head')
    <title>CA Report</title>
    <style>
        /* Specific overrides for wide CA report */
        .ca-table th, .ca-table td {
            font-size: 7.5pt;
            padding: 1.2mm 1mm;
        }
        .bg-indigo-light { background-color: #f1f5f9; }
        .bg-indigo-subtle { background-color: #f8fafc; }
        .border-strong { border: 1.5px solid #000 !important; }
    </style>
@endsection

@section('content')

<?php
    function getStaEmployerAmount($statutory, $emp){
        $name = $statutory->scheme_name;
        $sta = $emp->payroll_employee_breakups()->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')->get();
        $amount = 0;
        foreach($sta as $s){
            if($s->name_in_payslip == $name){
                $amount = $s->employer_contribution_amount;
            }
        }
        return $amount;
    }

    function getStaAmount($statutory, $emp){
        $name = $statutory->scheme_name;
        $sta = $emp->payroll_employee_breakups()->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')->get();
        $amount = 0;
        foreach($sta as $s){
            if($s->name_in_payslip == $name){
                $amount = $s->actual_payable_amount;
            }
        }
        return $amount;
    }

    function statuTotal($emp){
        $sta = $emp->payroll_employee_breakups()->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')->get();
        $amount = 0;
        foreach($sta as $s){
            $amount += $s->actual_payable_amount;
        }
        return $amount;
    }
?>

    <div class="header-bg" style="padding: 4mm 10mm;">
        <table class="table-borderless">
            <tr>
                <td class="w-half">
                    <h2 class="text-primary fw-bold text-uppercase" style="font-size: 14pt;">{{ $company->company_name }}</h2>
                    <p class="text-muted small m-0">Payroll Audit Summary: <span class="fw-bold">{{ $payroll->payroll_name }}</span></p>
                </td>
                <td class="w-half text-end">
                    <h1 class="fw-bold text-uppercase m-0" style="font-size: 16pt; color: #cbd5e1;">CA REPORT</h1>
                    <p class="text-muted small m-0">Generated: {{ date('d M Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="container" style="padding: 5mm;">

        <table class="table-bordered ca-table text-center w-full">
            <thead class="text-uppercase fw-bold">
                <tr style="background-color: #e2e8f0;">
                    <th colspan="5" class="border-strong">Employee Details</th>
                    <th colspan="3" class="border-strong">Attendance</th>
                    <th colspan="3" class="border-strong">Allowances</th>
                    <th colspan="2" class="border-strong">Salary Base</th>
                    <th colspan="{{ sizeof($statutories) }}" class="border-strong">Govt. Deductions (Emp)</th>
                    <th colspan="2" class="border-strong">Deductions</th>
                    <th colspan="{{ sizeof($statutories) }}" class="border-strong">Govt. Contributions (Org)</th>
                    <th class="border-strong">Final</th>
                </tr>
                <tr style="background-color: #f8fafc; font-size: 6.5pt;">
                    <th>ID</th>
                    <th class="text-start">Name</th>
                    <th>ESIC</th>
                    <th>PF/UAN</th>
                    <th>DOJ</th>
                    <th>Ttl</th>
                    <th>LOP</th>
                    <th>Prs</th>
                    <th>OT</th>
                    <th>Reim</th>
                    <th>Loan</th>
                    <th>Basic</th>
                    <th>Gross</th>
                    @foreach($statutories as $statutory)
                        <th class="text-primary">{{ $statutory->abbreviation }}</th>
                    @endforeach
                    <th>Other</th>
                    <th>Total</th>
                    @foreach($statutories as $statutory)
                        <th class="text-secondary">{{ $statutory->abbreviation }}</th>
                    @endforeach
                    <th class="bg-indigo-light">Net Payable</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $total_net = 0;
                    $total_statutory = 0;
                    $statu_summary = [];
                    foreach($statutories as $s) {
                        $statu_summary[$s->id] = ['emp' => 0, 'org' => 0, 'name' => $s->scheme_name, 'abbr' => $s->abbreviation];
                    }
                ?>
                @foreach($payroll->payroll_employees as $ind => $emp)
                    <tr style="{{ $ind % 2 == 0 ? '' : 'background-color: #fcfcfc;' }}">
                        <td>{{ $emp->employee->employee_code }}</td>
                        <td class="text-start fw-bold">{{ $emp->employee->first_name }} {{ $emp->employee->last_name }}</td>
                        <td>{{ $emp->employee->esic ?? '-' }}</td>
                        <td>{{ $emp->employee->pf ?? '-' }}</td>
                        <td>{{ date('d/m/y', strtotime($emp->employee->doj)) }}</td>
                        <td>{{ $payroll->working_days }}</td>
                        <td class="text-danger">{{ $emp->payroll_employee_attendances->lop }}</td>
                        <td class="fw-bold">{{ $emp->payroll_employee_attendances->payable_days }}</td>
                        <td>{{ $emp->overtime_earning }}</td>
                        <td>{{ $emp->reimbursement }}</td>
                        <td>{{ $emp->loan_disbursal }}</td>
                        <td>{{ $emp->basic_pay }}</td>
                        <td>{{ $emp->gross_pay }}</td>
                        
                        @foreach($statutories as $statutory)
                            <?php 
                                $amt = getStaAmount($statutory, $emp); 
                                $statu_summary[$statutory->id]['emp'] += $amt;
                                $total_statutory += $amt;
                            ?>
                            <td>{{ $amt > 0 ? $amt : '' }}</td>
                        @endforeach
                        
                        <td>{{ $emp->gross_deduction - statuTotal($emp) }}</td>
                        <td class="fw-bold">{{ $emp->gross_deduction }}</td>
                        
                        @foreach($statutories as $statutory)
                            <?php 
                                $amt = getStaEmployerAmount($statutory, $emp); 
                                $statu_summary[$statutory->id]['org'] += $amt;
                                $total_statutory += $amt;
                            ?>
                            <td>{{ $amt > 0 ? $amt : '' }}</td>
                        @endforeach
                        
                        <td class="fw-bold bg-indigo-subtle">Rs. {{ number_format($emp->net_payable_amount, 2) }}</td>
                        <?php $total_net += $emp->net_payable_amount; ?>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="fw-bold" style="background-color: #f1f5f9;">
                <tr>
                    <td colspan="13" class="text-end">GRAND TOTALS</td>
                    @foreach($statutories as $statutory)
                        <td>{{ $statu_summary[$statutory->id]['emp'] }}</td>
                    @endforeach
                    <td></td>
                    <td></td>
                    @foreach($statutories as $statutory)
                        <td>{{ $statu_summary[$statutory->id]['org'] }}</td>
                    @endforeach
                    <td class="bg-primary text-white">Rs. {{ number_format($total_net, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 10mm;">
            <div class="section-title">Statutory Liability Summary</div>
            <table class="table-bordered" style="width: 60%;">
                <thead>
                    <tr class="bg-indigo-light">
                        <th>Compliance Scheme</th>
                        <th class="text-end">Employee Share</th>
                        <th class="text-end">Employer Share</th>
                        <th class="text-end">Total Liability</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statu_summary as $summary)
                        <tr>
                            <td>{{ $summary['name'] }} ({{ $summary['abbr'] }})</td>
                            <td class="text-end">{{ number_format($summary['emp'], 2) }}</td>
                            <td class="text-end">{{ number_format($summary['org'], 2) }}</td>
                            <td class="text-end fw-bold">Rs. {{ number_format($summary['emp'] + $summary['org'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold text-primary">
                        <td>TOTAL STATUTORY PAYABLE</td>
                        <td class="text-end">{{ number_format($total_statutory - array_sum(array_column($statu_summary, 'org')), 2) }}</td>
                        <td class="text-end">{{ number_format(array_sum(array_column($statu_summary, 'org')), 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($total_statutory, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

@endsection