
<table>
    <thead>
        <!-- Main Company Header -->
        <tr>
            <th colspan="{{ 18 + (sizeof($statutories) * 2) }}" style="background-color: #1e293b; color: #ffffff; font-size: 14pt; font-weight: bold; text-align: center; height: 30pt; vertical-align: middle;">
                {{ $company->company_name }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 18 + (sizeof($statutories) * 2) }}" style="background-color: #334155; color: #ffffff; font-size: 11pt; font-weight: bold; text-align: center; height: 20pt; vertical-align: middle;">
                Statutory Audit Report - {{ $payroll->payroll_name }}
            </th>
        </tr>

        <!-- Category Headers -->
        <tr style="background-color: #e2e8f0;">
            <th colspan="7" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center;">Employee Details</th>
            <th colspan="3" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center;">Attendance</th>
            <th colspan="3" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center;">Allowances</th>
            <th colspan="2" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center;">Salary Base</th>
            <th colspan="{{ sizeof($statutories) }}" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center; background-color: #dcfce7;">Govt. Deductions (Emp)</th>
            <th colspan="2" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center;">Deductions</th>
            <th colspan="{{ sizeof($statutories) }}" style="border: 1px solid #94a3b8; font-weight: bold; text-align: center; background-color: #dbeafe;">Govt. Contributions (Org)</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold; text-align: center;">Final</th>
        </tr>

        <!-- Column Headers -->
        <tr style="background-color: #f1f5f9;">
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Employee ID</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Full Name</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">PAN No.</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">UAN No.</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">PF No.</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">ESIC No.</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Date of Joining</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Working Days</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">LOP Days</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Payable Days</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Overtime Pay</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Reimbursement</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Loan/Advance</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Basic Pay</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Gross Salary</th>
            @foreach($statutories as $statutory)
                <th style="border: 1px solid #94a3b8; font-weight: bold;">{{ $statutory->abbreviation }}</th>
            @endforeach
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Other Ded.</th>
            <th style="border: 1px solid #94a3b8; font-weight: bold;">Total Ded.</th>
            @foreach($statutories as $statutory)
                <th style="border: 1px solid #94a3b8; font-weight: bold;">{{ $statutory->abbreviation }}</th>
            @endforeach
            <th style="border: 1px solid #94a3b8; font-weight: bold; background-color: #6366f1; color: #ffffff;">Net Payable</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_net = 0;
            $total_statutory = 0;
            $statu_summary = [];
            foreach($statutories as $s) {
                $statu_summary[$s->id] = ['emp' => 0, 'org' => 0, 'name' => $s->scheme_name, 'abbr' => $s->abbreviation];
            }
        @endphp

        @foreach($payroll->payroll_employees as $ind => $emp)
            <tr style="{{ $ind % 2 == 0 ? '' : 'background-color: #f8fafc;' }}">
                <td style="border: 1px solid #e2e8f0; text-align: center; mso-number-format:'\@';">{{ $emp->employee->employee_code }}</td>
                <td style="border: 1px solid #e2e8f0;">{{ $emp->employee->first_name }} {{ $emp->employee->last_name }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center; mso-number-format:'\@';">{{ $emp->employee->pan ?? '-' }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center; mso-number-format:'\@';">{{ $emp->employee->uan ?? '-' }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center; mso-number-format:'\@';">{{ $emp->employee->pf ?? '-' }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center; mso-number-format:'\@';">{{ $emp->employee->esic ?? '-' }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center;">{{ date('d-m-Y', strtotime($emp->employee->doj)) }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center;">{{ $payroll->working_days }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center; color: #e11d48;">{{ $emp->payroll_employee_attendances->lop }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: center; font-weight: bold;">{{ $emp->payroll_employee_attendances->payable_days }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: right;">{{ number_format($emp->overtime_earning, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: right;">{{ number_format($emp->reimbursement, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: right;">{{ number_format($emp->loan_disbursal, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: right;">{{ number_format($emp->basic_pay, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: right; background-color: #fefce8;">{{ number_format($emp->gross_pay, 2) }}</td>

                @foreach($statutories as $statutory)
                    @php 
                        $amt = 0;
                        $name = $statutory->scheme_name;
                        $sta = $emp->payroll_employee_breakups()->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')->get();
                        foreach($sta as $s){
                            if($s->name_in_payslip == $name){
                                $amt = $s->actual_payable_amount;
                            }
                        }
                        $statu_summary[$statutory->id]['emp'] += $amt;
                        $total_statutory += $amt;
                    @endphp
                    <td style="border: 1px solid #e2e8f0; text-align: right;">{{ $amt > 0 ? number_format($amt, 2) : '0.00' }}</td>
                @endforeach

                <td style="border: 1px solid #e2e8f0; text-align: right;">{{ number_format($emp->gross_deduction - $emp->payroll_employee_breakups()->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')->sum('actual_payable_amount'), 2) }}</td>
                <td style="border: 1px solid #e2e8f0; text-align: right; font-weight: bold;">{{ number_format($emp->gross_deduction, 2) }}</td>

                @foreach($statutories as $statutory)
                    @php 
                        $amt = 0;
                        $name = $statutory->scheme_name;
                        $sta = $emp->payroll_employee_breakups()->where('breakupable_type', 'App\Models\StatutoryComplianceCondition')->get();
                        foreach($sta as $s){
                            if($s->name_in_payslip == $name){
                                $amt = $s->employer_contribution_amount;
                            }
                        }
                        $statu_summary[$statutory->id]['org'] += $amt;
                        $total_statutory += $amt;
                    @endphp
                    <td style="border: 1px solid #e2e8f0; text-align: right;">{{ $amt > 0 ? number_format($amt, 2) : '0.00' }}</td>
                @endforeach

                <td style="border: 1px solid #e2e8f0; text-align: right; font-weight: bold; background-color: #f5f3ff;">{{ number_format($emp->net_payable_amount, 2) }}</td>
                @php $total_net += $emp->net_payable_amount; @endphp
            </tr>
        @endforeach
    </tbody>
    <tfoot style="background-color: #e2e8f0;">
        <tr style="height: 25pt;">
            <td colspan="14" style="border: 1px solid #94a3b8; font-weight: bold; text-align: right;">GRAND TOTALS</td>
            <td style="border: 1px solid #94a3b8; font-weight: bold; text-align: right;">{{ number_format($payroll->payroll_employees->sum('gross_pay'), 2) }}</td>
            @foreach($statutories as $statutory)
                <td style="border: 1px solid #94a3b8; font-weight: bold; text-align: right;">{{ number_format($statu_summary[$statutory->id]['emp'], 2) }}</td>
            @endforeach
            <td style="border: 1px solid #94a3b8;"></td>
            <td style="border: 1px solid #94a3b8; font-weight: bold; text-align: right;">{{ number_format($payroll->payroll_employees->sum('gross_deduction'), 2) }}</td>
            @foreach($statutories as $statutory)
                <td style="border: 1px solid #94a3b8; font-weight: bold; text-align: right;">{{ number_format($statu_summary[$statutory->id]['org'], 2) }}</td>
            @endforeach
            <td style="border: 1px solid #94a3b8; font-weight: bold; text-align: right; background-color: #6366f1; color: #ffffff;">{{ number_format($total_net, 2) }}</td>
        </tr>
    </tfoot>
</table>
