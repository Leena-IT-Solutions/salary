@extends('layouts.pdf')

@section('head')
    <title>Attendance Audit Report</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; background: #fff; }
        .report-header { border-bottom: 2px solid #6366f1; padding-bottom: 5mm; margin-bottom: 5mm; }
        .company-name { font-size: 6mm; font-weight: bold; color: #4338ca; }
        .report-title { font-size: 4mm; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        
        table { border: 1px solid #e2e8f0; border-collapse: collapse; width: 100%; table-layout: fixed; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; text-align: center; border: 1px solid #e2e8f0; padding: 1.5mm 0.5mm; font-size: 2mm; }
        td { border: 1px solid #e2e8f0; vertical-align: top; padding: 1mm 0.5mm; font-size: 1.8mm; line-height: 1.2; }
        
        .emp-name-col { 
            width: 65mm; 
            text-align: left; 
            padding-left: 3mm; 
            background: #fff; 
            border-right: 2px solid #e2e8f0;
            word-wrap: break-word;
            white-space: normal;
        }
        .emp-code-col { width: 15mm; text-align: center; }
        .date-col { width: 10mm; text-align: center; }
        .summary-col { width: 15mm; text-align: center; background: #f1f5f9; font-weight: bold; }

        .status-present { color: #10b981; font-weight: bold; }
        .status-absent { background-color: #fef2f2; color: #ef4444; font-weight: bold; }
        .status-weekoff { color: #6b7280; font-style: italic; }
        
        .time-tag { display: block; font-size: 1.5mm; color: #6366f1; margin-top: 0.5mm; }
        .alert-tag { display: block; font-size: 1.5mm; color: #dc2626; font-weight: bold; border-top: 0.1mm solid #fee2e2; margin-top: 1mm; padding-top: 0.5mm; }
        .lop-tag { display: block; font-size: 1.5mm; color: #1e293b; font-weight: bold; }
        
        .zebra tr:nth-child(even) { background-color: #fcfdfe; }
    </style>
@endsection

@section('content')
@php
    $company = \App\Models\CompanyProfile::first();
@endphp

<div class="report-header">
    <table class="table-borderless">
        <tr>
            <td class="v-middle" style="width: 70%;">
                <div class="company-name">{{ $company->company_name ?? 'SalaryManager' }}</div>
                <div class="report-title">Detailed Attendance Audit Report</div>
                <div style="font-size: 2.5mm; margin-top: 2mm; color: #64748b;">
                    Period: <strong>{{ date("d M Y", strtotime($from)) }}</strong> to <strong>{{ date("d M Y", strtotime($to)) }}</strong>
                </div>
            </td>
            <td class="text-end v-middle">
                @if($company->logo)
                    <img src="{{ public_path('storage/logo/'.$company->logo) }}" style="height: 15mm;">
                @endif
                <div style="font-size: 2mm; color: #94a3b8; margin-top: 2mm;">Generated on: {{ date('d-m-Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="zebra">
    <thead>
        <tr>
            <th class="emp-name-col" style="text-align: left; padding-left: 2mm;">Employee</th>
            <th class="emp-code-col">Code</th>
            @foreach($dds as $dt)
                <th class="date-col">{{ $dt }}</th>
            @endforeach
            <th class="summary-col" style="background: #eef2ff; color: #4338ca;">Total LOP</th>
            <th class="summary-col" style="background: #eef2ff; color: #4338ca;">Total Late</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            <tr>
                <td class="emp-name-col">
                    <div style="font-weight: bold; font-size: 2mm; color: #1e293b;">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                </td>
                <td class="emp-code-col">{{ $employee->employee_code }}</td>
                
                @php
                    $totalLop = 0;
                    $totalLate = 0;
                @endphp

                @foreach($dates as $date)
                    @php
                        $es = $employee->employee_shifts->where('dt', $date)->first();
                    @endphp
                    <td class="date-col @if($es && $es->status == 'Absent') status-absent @endif">
                        @if($es)
                            @php
                                $totalLop += $es->lop;
                                $totalLate += $es->late;
                                $statusClass = '';
                                if($es->status == 'Present') $statusClass = 'status-present';
                                if($es->status == 'Weekoff' || $es->status == 'Holiday') $statusClass = 'status-weekoff';
                            @endphp
                            
                            <div class="{{ $statusClass }}">{{ $es->status }}</div>
                            
                            @foreach($es->employee_attendance as $ea)
                                <span class="time-tag">{{ date('H:i', strtotime($ea->tm)) }}</span>
                            @endforeach

                            @if($es->late > 0)
                                <span class="alert-tag">L: {{ $es->late }}m</span>
                            @endif
                            @if($es->lop > 0)
                                <span class="lop-tag">LOP: {{ $es->lop }}</span>
                            @endif
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                @endforeach
                
                <td class="summary-col" style="color: #4338ca;">{{ $totalLop }}</td>
                <td class="summary-col" style="color: #ef4444;">{{ $totalLate }}m</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection