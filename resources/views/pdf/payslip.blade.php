@extends('layouts.pdf')

@section('head')
    <title>Payslip</title>
@endsection

@section('content')

    @foreach($payroll->payroll_employees as $ind=>$emp)
        <div class="p-5 {{ ($ind + 1) == sizeof($payroll->payroll_employees) ? '' : 'page-break' }}">

            <table class="">
                <thead>
                    <tr>
                        <th colspan="4" class="p-3">
                            <h3 class="">{{ $company->company_name }}</h3>
                            <p class="fw-normal pb-2">{{ $company->address }} {{ $company->city }} {{ $company->pincode }} {{ $company->state }} {{ $company->country }}</p>
                            <p class="">{{ $payroll->payroll_name }}</p>
                        </th>
                    </tr>
                </thead>
            </table>

            <table class="">
                <thead>
                    
                    <tr>
                        <td class="p-1" style="width:120px">Salary Slip of</td>
                        <td class="p-1">{{ $emp->employee->first_name }} {{ $emp->employee->middle_name }} {{ $emp->employee->last_name }}</td>
                        <td class="p-1" style="width:120px">Employee Code</td>
                        <td class="p-1">{{ $emp->employee->employee_code }}</td>
                    </tr>
                    <tr>
                        <td class="p-1" >Designation</td>
                        <td class="p-1">{{ $emp->employee->employee_designation->designation->designation }}</td>
                        <td class="p-1">Loss of Pays</td>
                        <td class="p-1">{{ $emp->payroll_employee_attendances->lop }}</td>
                    </tr>
                    <tr>
                        <td class="p-1">Bank A/C No</td>
                        <td class="p-1" colspan="3">
                            @if($emp->employee->employee_bank)
                            {{ $emp->employee->employee_bank->bank_name }} - 
                            {{ $emp->employee->employee_bank->account_number }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="p-1" >EPF A/C</td>
                        <td class="p-1">{{ $emp->employee->pf }}</td>
                        <td class="p-1">Date of Joining</td>
                        <td class="p-1">{{ date('d-m-Y', strtotime($emp->employee->doj)) }}</td>
                    </tr>
                </thead>
            </table>

            <table class="">
                
                <thead class="">
                    <tr>
                        <th class="p-2 w-half">Earning</th>
                        <th class="p-2">Deduction</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="v-top p-0">
                            <table class="">
                                @foreach($emp->payroll_employee_earnings as $breakup)
                                <tr>
                                    <td class="p-1">{{ $breakup->name_in_payslip }}</td>
                                    <td class="p-1 text-end">{{ $breakup->actual_payable_amount }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                        <td class="v-top p-0">
                            <table class="">
                                @foreach($emp->payroll_employee_deductions as $breakup)
                                <tr>
                                    <td class="p-1">{{ $breakup->name_in_payslip }}</td>
                                    <td class="p-1 text-end">{{ $breakup->actual_payable_amount }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                    <tr class="text-end">
                        <td class="p-1">Gross Salary: {{ $emp->gross_salary }}</td>
                        <td class="p-1">Gross Deduction: {{ $emp->gross_deduction }}</td>
                    </tr>
                    <tr class="text-end">
                        <td colspan="2" class="p-2">
                            <h4 class="pb-1">Net Payable Amount: Rs {{ $emp->net_payable_amount }}/-</h4>
                            <h4 class="text-capitalize">{{ $emp->amount_str }}</h4>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    @endforeach

@endsection