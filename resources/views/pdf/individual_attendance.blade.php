@extends('layouts.pdf')

@section('head')
    <title>Individual Attendance Report - {{ date('M Y', strtotime($from)) }}</title>
    <style>
        @page { 
            margin: 5mm; 
            size: A4 portrait;
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #111827; 
            line-height: 1.1;
            font-size: 8pt;
            margin: 0;
            padding: 5mm;
        }
        .page-wrapper {
            position: relative;
            height: 100%;
            page-break-after: always;
        }
        .page-wrapper:last-child {
            page-break-after: auto;
        }
        
        /* Header Section */
        .report-header { 
            border-bottom: 1px solid #d1d5db; 
            padding-bottom: 3mm; 
            margin-bottom: 4mm; 
        }
        .company-name { font-size: 14pt; font-weight: bold; color: #111827; }
        .report-title { font-size: 9pt; color: #374151; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Info Cards */
        .info-grid { 
            background: #f9fafb; 
            border: 1px solid #d1d5db;
            padding: 3mm; 
            border-radius: 1mm; 
            margin-bottom: 4mm; 
        }
        .info-label { font-size: 6.5pt; color: #4b5563; text-transform: uppercase; margin-bottom: 0.5mm; }
        .info-value { font-size: 9pt; font-weight: bold; color: #111827; }
        
        /* Matrix Table */
        .attendance-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 4mm;
            table-layout: fixed;
        }
        .attendance-table th { 
            background: #f3f4f6; 
            color: #111827; 
            font-weight: bold; 
            text-align: left; 
            padding: 1mm 1mm; 
            border: 1px solid #d1d5db; 
            font-size: 7pt; 
            text-transform: uppercase; 
        }
        .attendance-table td { 
            padding: 0.8mm 1mm; 
            border: 1px solid #d1d5db; 
            font-size: 7.2pt; 
            vertical-align: middle; 
            color: #111827;
        }
        
        /* Badges */
        .badge { padding: 0.4mm 1.5mm; border-radius: 0.5mm; font-weight: bold; font-size: 6.5pt; display: inline-block; }
        .bg-present { background: #dcfce7; color: #065f46; }
        .bg-absent { background: #fee2e2; color: #991b1b; }
        .bg-weekoff { background: #f3f4f6; color: #374151; }
        .bg-leave { background: #e0e7ff; color: #312e81; }
        .bg-onduty { background: #fae8ff; color: #701a75; }
        
        .time-badge { 
            background: #ffffff; 
            border: 0.1mm solid #d1d5db;
            color: #1e3a5f; 
            padding: 0.3mm 1mm; 
            border-radius: 0.5mm; 
            margin-right: 0.5mm; 
            font-size: 6.5pt; 
            display: inline-block; 
            font-family: monospace;
            font-weight: bold;
        }
        
        /* Stats Section */
        .stats-grid { 
            width: 100%; 
            margin-bottom: 6mm;
        }
        .stat-card {
            border: 1px solid #d1d5db;
            padding: 2.5mm;
            text-align: center;
        }
        .stat-card .val { font-size: 11pt; font-weight: bold; color: #111827; display: block; }
        .stat-card .lbl { font-size: 6.5pt; color: #374151; text-transform: uppercase; }
        
        /* Footer */
        .signature-section { 
            margin-top: auto;
            border-top: 1px solid #f1f5f9;
            padding-top: 10mm;
        }
        
        .row-hover:nth-child(even) { background-color: #f9fafb; }
        .text-danger { color: #991b1b; }
        .text-primary { color: #1e3a5f; }
        .fw-bold { font-weight: bold; }
        
        /* Indicator Icons */
        .indicator {
            display: inline-block;
            width: 14mm;
            font-size: 6pt;
            padding: 0.3mm 0.8mm;
            border-radius: 0.3mm;
            text-align: center;
            margin-right: 1mm;
            font-weight: bold;
        }
        .ind-tu { background: #fff7ed; color: #7c2d12; border: 0.1mm solid #ffedd5; }
        .ind-sl { background: #f0f9ff; color: #0c4a6e; border: 0.1mm solid #e0f2fe; }
        .ind-ot { background: #fdf2f8; color: #831843; border: 0.1mm solid #fce7f3; }
    </style>
@endsection

@section('content')
@php
    $company = \App\Models\CompanyProfile::first();
@endphp

@foreach($employees as $employee)
<div class="page-wrapper">
    <!-- Header -->
    <div class="report-header">
        <table style="width: 100%;" class="table-borderless">
            <tr>
                <td>
                    <div class="company-name">{{ $company->company_name ?? 'SalaryManager' }}</div>
                    <div class="report-title">Monthly Attendance & Performance Summary</div>
                </td>
                <td style="text-align: right;">
                    @if($company->logo)
                        <img src="{{ public_path('storage/logo/'.$company->logo) }}" style="height: 10mm;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Employee Info -->
    <div class="info-grid">
        <table style="width: 100%;" class="table-borderless">
            <tr>
                <td style="width: 33%;">
                    <div class="info-label">Employee Profile</div>
                    <div class="info-value">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                    <div style="font-size: 7pt; color: #374151;">#{{ $employee->employee_code }} | {{ $employee->employee_department->department->department ?? 'N/A' }}</div>
                </td>
                <td style="width: 33%; text-align: center;">
                    <div class="info-label">Reporting Period</div>
                    <div class="info-value">{{ date('01 M Y', strtotime($from)) }} - {{ date('t M Y', strtotime($from)) }}</div>
                    <div style="font-size: 7pt; color: #374151;">(Generated on: {{ date('d-m-Y H:i') }})</div>
                </td>
                <td style="width: 33%; text-align: right;">
                    <div class="info-label">Working Shift</div>
                    <div class="info-value">{{ $employee->working_shift->shift_name ?? 'Standard' }}</div>
                    <div style="font-size: 7pt; color: #374151;">Timing: {{ $employee->working_shift->in ?? '00:00' }} - {{ $employee->working_shift->out ?? '00:00' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Salary Breakup Table -->
    @if($employee->employee_salary)
    <table style="width: 100%; margin-bottom: 4mm; font-size: 7.5pt; border-collapse: collapse; border: 1px solid #e2e8f0; table-layout: fixed;">
        <thead>
            <tr style="background-color: #f8fafc;">
            <th style="padding: 1.2mm 2mm; text-align: left; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 7pt; width: 25%;">Salary Parameter</th>
                <th style="padding: 1.2mm 2mm; text-align: center; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 7pt; width: 25%;">Monthly CTC</th>
                <th style="padding: 1.2mm 2mm; text-align: center; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 7pt; width: 25%;">Gross Salary</th>
                <th style="padding: 1.2mm 2mm; text-align: center; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 7pt; width: 25%;">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 1.2mm 2mm; font-weight: bold; border: 1px solid #d1d5db; color: #111827; text-align: left;">Standard Structure</td>
                <td style="padding: 1.2mm 2mm; text-align: center; font-weight: bold; border: 1px solid #d1d5db; color: #111827;">Rs. {{ number_format($employee->employee_salary->ctc, 2) }}</td>
                <td style="padding: 1.2mm 2mm; text-align: center; font-weight: bold; border: 1px solid #d1d5db; color: #111827;">Rs. {{ number_format($employee->employee_salary->gross_pay, 2) }}</td>
                <td style="padding: 1.2mm 2mm; text-align: center; font-weight: bold; border: 1px solid #d1d5db; color: #111827;">Rs. {{ number_format($employee->employee_salary->net_pay, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @php
        $totalLop = 0; $totalLate = 0; $presentDays = 0; $absentDays = 0; 
        $leaveDays = 0; $onDutyDays = 0; $totalOT = 0; $shortLeaves = 0;
        $workDays = count($dates);
    @endphp

    <!-- Detailed Matrix -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th style="width: 18%;">Date</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 18%;">Punch Sequence</th>
                <th style="width: 8%; text-align: center;">Late</th>
                <th style="width: 8%; text-align: center;">Early</th>
                <th style="width: 8%; text-align: center;">LOP</th>
                <th style="width: 28%;">Approvals & Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dates as $date)
                @php
                    $es = $employee->employee_shifts->get($date);
                @endphp
                <tr class="row-hover">
                    <td class="fw-bold">{{ date('d M Y', strtotime($date)) }} <span style="color: #6b7280; font-weight: normal; font-size: 6.5pt;">({{ date('D', strtotime($date)) }})</span></td>
                    <td>
                        @if($es)
                            @php
                                $statusClass = 'bg-weekoff';
                                if($es->status == 'Present') { $statusClass = 'bg-present'; $presentDays++; }
                                if($es->status == 'Absent') { $statusClass = 'bg-absent'; $absentDays++; }
                                if($es->status == 'Leave') { $statusClass = 'bg-leave'; $leaveDays++; }
                                if($es->status == 'On Duty') { $statusClass = 'bg-onduty'; $onDutyDays++; }
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $es->status }}</span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($es)
                            @foreach($es->employee_attendance as $ea)
                                <span class="time-badge">{{ date('H:i', strtotime($ea->tm)) }}</span>
                            @endforeach
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($es && $es->late > 0)
                            <span class="text-danger fw-bold">{{ $es->late }}m</span>
                            @php $totalLate += $es->late @endphp
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($es && $es->early > 0)
                            <span class="text-primary">{{ $es->early }}m</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        @if($es && $es->lop > 0)
                            {{ $es->lop }}
                            @php $totalLop += $es->lop @endphp
                        @endif
                    </td>
                    <td>
                        @if($es)
                            @if($es->time_update) <span class="indicator ind-tu">TIME-FIXED</span> @endif
                            @if($es->on_duty) <span class="indicator ind-tu">ON-DUTY</span> @endif
                            @if($es->short_leave) <span class="indicator ind-sl">SHORT LEAVE</span> @php $shortLeaves++ @endphp @endif
                            @if($es->overtime) <span class="indicator ind-ot">OT: {{ $es->overtime->hrs }}h</span> @php $totalOT += $es->overtime->hrs @endphp @endif
                            @if($es->leave) <span class="indicator ind-sl">{{ $es->leave->leave_master->short_name ?? 'LV' }}</span> @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Analytics Summary -->
    <table class="stats-grid table-borderless">
        <tr>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card"><span class="val">{{ $workDays }}</span><span class="lbl">Total Days</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #10b981;"><span class="val">{{ $presentDays }}</span><span class="lbl">Present</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #ef4444;"><span class="val">{{ $absentDays }}</span><span class="lbl">Absent</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #6366f1;"><span class="val">{{ $leaveDays }}</span><span class="lbl">Leave</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #86198f;"><span class="val">{{ $onDutyDays }}</span><span class="lbl">On Duty</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #0ea5e9;"><span class="val">{{ $shortLeaves }}</span><span class="lbl">Short LV</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #ec4899;"><span class="val">{{ $totalOT }}h</span><span class="lbl">Overtime</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #f59e0b;"><span class="val">{{ $totalLate }}m</span><span class="lbl">Late Ad.</span></div>
            </td>
            <td style="width:11%; padding: 0.5mm;">
                <div class="stat-card" style="background: #f9fafb; border: 1px solid #1e3a5f;"><span class="val" style="color: #111827;">{{ $totalLop }}</span><span class="lbl">Total LOP</span></div>
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <div class="signature-section">
        <table style="width: 100%;" class="table-borderless">
            <tr>
                <td style="width: 30%; border-top: 0.2mm dashed #cbd5e1; padding-top: 2mm; text-align: center;">
                    <div style="font-size: 7pt; color: #374151; text-transform: uppercase;">Employee Acknowledgment</div>
                </td>
                <td style="width: 35%;"></td>
                <td style="width: 30%; border-top: 0.2mm dashed #9ca3af; padding-top: 2mm; text-align: center;">
                    <div style="font-size: 7pt; color: #374151; text-transform: uppercase;">Head of Department / HR</div>
                </td>
            </tr>
        </table>
    </div>
</div>
@endforeach

@endsection
