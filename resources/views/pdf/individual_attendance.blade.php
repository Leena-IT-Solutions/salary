@extends('layouts.pdf')

@section('head')
    <title>Individual Attendance Report - {{ date('M Y', strtotime($from)) }}</title>
    <style>
        @page { 
            margin: 6mm 15mm; 
            size: A4 portrait;
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #111827; 
            line-height: 1.15;
            font-size: 7.5pt;
            margin: 0;
            padding: 0 5mm;
        }
        .page-wrapper {
            position: relative;
            page-break-after: always;
            page-break-inside: avoid;
        }
        .page-wrapper:last-child {
            page-break-after: avoid;
        }
        
        /* Header Section */
        .report-header { 
            border-bottom: 1.5px solid #d1d5db; 
            padding-bottom: 2.5mm; 
            margin-bottom: 3.5mm; 
        }
        .company-name { font-size: 15pt; font-weight: bold; color: #111827; }
        .report-title { font-size: 8.5pt; color: #374151; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Info Cards */
        .info-grid { 
            background: #f9fafb; 
            border: 1px solid #d1d5db;
            padding: 2.5mm 3.5mm; 
            border-radius: 1mm; 
            margin-bottom: 3.5mm; 
        }
        .info-label { font-size: 6.5pt; color: #4b5563; text-transform: uppercase; margin-bottom: 0.3mm; }
        .info-value { font-size: 9.5pt; font-weight: bold; color: #111827; }
        
        /* Matrix Table */
        .attendance-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 3.5mm;
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
            padding: 1mm 1mm; 
            border: 1px solid #d1d5db; 
            font-size: 7pt; 
            vertical-align: middle; 
            color: #111827;
        }
        
        /* Badges */
        .badge { padding: 0.4mm 1.5mm; border-radius: 0.5mm; font-weight: bold; font-size: 6.2pt; display: inline-block; }
        .bg-present { background: #dcfce7; color: #065f46; }
        .bg-absent { background: #fee2e2; color: #991b1b; }
        .bg-weekoff { background: #f3f4f6; color: #374151; }
        .bg-holiday { background: #fef3c7; color: #92400e; }
        .bg-halfday { background: #e0f2fe; color: #075985; }
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
            margin-bottom: 4mm;
            border-collapse: collapse;
        }
        .stat-card {
            border: 1px solid #d1d5db;
            padding: 2mm 1mm;
            text-align: center;
            background: #ffffff;
        }
        .stat-card .val { font-size: 10pt; font-weight: bold; color: #111827; display: block; line-height: 1; }
        .stat-card .lbl { font-size: 6pt; color: #374151; text-transform: uppercase; font-weight: bold; margin-top: 0.8mm; display: block; }
        
        /* Footer */
        .signature-section { 
            margin-top: 6mm;
            border-top: 1px solid #e5e7eb;
            padding-top: 4mm;
        }
        
        .row-hover:nth-child(even) { background-color: #f9fafb; }
        .text-danger { color: #dc2626; }
        .text-primary { color: #1e3a5f; }
        .fw-bold { font-weight: bold; }
        
        /* Indicator Icons */
        .indicator {
            display: inline-block;
            font-size: 6pt;
            padding: 0.3mm 0.8mm;
            border-radius: 0.3mm;
            text-align: center;
            margin-right: 0.8mm;
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
    <table style="width: 100%; margin-bottom: 3.5mm; font-size: 7pt; border-collapse: collapse; border: 1px solid #d1d5db; table-layout: fixed;">
        <thead>
            <tr style="background-color: #f8fafc;">
                <th style="padding: 1mm 1.8mm; text-align: left; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 6.5pt; width: 25%;">Salary Parameter</th>
                <th style="padding: 1mm 1.8mm; text-align: center; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 6.5pt; width: 25%;">Monthly CTC</th>
                <th style="padding: 1mm 1.8mm; text-align: center; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 6.5pt; width: 25%;">Gross Salary</th>
                <th style="padding: 1mm 1.8mm; text-align: center; color: #111827; font-weight: bold; border: 1px solid #d1d5db; text-transform: uppercase; font-size: 6.5pt; width: 25%;">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 1mm 1.8mm; font-weight: bold; border: 1px solid #d1d5db; color: #111827; text-align: left;">Standard Structure</td>
                <td style="padding: 1mm 1.8mm; text-align: center; font-weight: bold; border: 1px solid #d1d5db; color: #111827;">Rs. {{ number_format($employee->employee_salary->ctc, 2) }}</td>
                <td style="padding: 1mm 1.8mm; text-align: center; font-weight: bold; border: 1px solid #d1d5db; color: #111827;">Rs. {{ number_format($employee->employee_salary->gross_pay, 2) }}</td>
                <td style="padding: 1mm 1.8mm; text-align: center; font-weight: bold; border: 1px solid #d1d5db; color: #111827;">Rs. {{ number_format($employee->employee_salary->net_pay, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @php
        $totalDays = count($dates);
        $workingDays = 0;
        $presentDays = 0;
        $absentDays = 0; 
        $leaveDays = 0; 
        $weekoffDays = 0;
        $holidayDays = 0;
        $halfDays = 0;
        $onDutyDays = 0; 
        $shortLeaves = 0;
        $totalOT = 0; 
        $totalLate = 0;
        $totalEarly = 0;
        $totalLop = 0;
    @endphp

    <!-- Detailed Matrix -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th style="width: 17%;">Date</th>
                <th style="width: 13%;">Status</th>
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
                    $statusStr = $es ? $es->status : '';

                    if($es) {
                        if($statusStr == 'Weekoff') {
                            $weekoffDays++;
                        } elseif($statusStr == 'Holiday') {
                            $holidayDays++;
                        } else {
                            $workingDays++;
                        }

                        if($statusStr == 'Present' || $statusStr == 'Working') {
                            $presentDays++;
                        } elseif($statusStr == 'Absent') {
                            $absentDays++;
                        } elseif($statusStr == 'Leave' || $statusStr == 'Halfday Leave') {
                            $isHalf = ($statusStr == 'Halfday Leave' || ($es->leave && $es->leave->is_halfday == 'Yes'));
                            $leaveDays += $isHalf ? 0.5 : 1;
                            if($isHalf) $halfDays++;
                        } elseif($statusStr == 'On Duty') {
                            $onDutyDays++;
                            $presentDays++;
                        } elseif($statusStr == 'Halfday Working' || $statusStr == 'Halfday') {
                            $presentDays += 0.5;
                            $halfDays++;
                        } elseif($statusStr == 'Time Update' || $statusStr == 'Short Leave') {
                            $presentDays++;
                        }

                        if($es->short_leave) {
                            $shortLeaves++;
                        }
                        if($es->overtime) {
                            $totalOT += $es->overtime->hrs;
                        }
                        if($es->late > 0) {
                            $totalLate += $es->late;
                        }
                        if($es->early > 0) {
                            $totalEarly += $es->early;
                        }
                        if($es->lop > 0) {
                            $totalLop += $es->lop;
                        }
                    } else {
                        $dayOfWeek = date('N', strtotime($date));
                        if($dayOfWeek == 7) {
                            $weekoffDays++;
                        } else {
                            $workingDays++;
                        }
                    }

                    $statusClass = 'bg-weekoff';
                    if($statusStr == 'Present' || $statusStr == 'Working') { $statusClass = 'bg-present'; }
                    elseif($statusStr == 'Absent') { $statusClass = 'bg-absent'; }
                    elseif($statusStr == 'Leave' || $statusStr == 'Halfday Leave') { $statusClass = 'bg-leave'; }
                    elseif($statusStr == 'On Duty') { $statusClass = 'bg-onduty'; }
                    elseif($statusStr == 'Holiday') { $statusClass = 'bg-holiday'; }
                    elseif($statusStr == 'Halfday Working' || $statusStr == 'Halfday') { $statusClass = 'bg-halfday'; }
                @endphp
                <tr class="row-hover">
                    <td class="fw-bold">{{ date('d M Y', strtotime($date)) }} <span style="color: #6b7280; font-weight: normal; font-size: 6.5pt;">({{ date('D', strtotime($date)) }})</span></td>
                    <td>
                        @if($es)
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
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($es && $es->early > 0)
                            <span class="text-danger fw-bold">{{ $es->early }}m</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        @if($es && $es->lop > 0)
                            {{ $es->lop }}
                        @endif
                    </td>
                    <td>
                        @if($es)
                            @if($es->time_update) <span class="indicator ind-tu">TIME-FIXED</span> @endif
                            @if($es->on_duty) <span class="indicator ind-tu">ON-DUTY</span> @endif
                            @if($es->short_leave) <span class="indicator ind-sl">SHORT LEAVE</span> @endif
                            @if($es->overtime) <span class="indicator ind-ot">OT: {{ $es->overtime->hrs }}h</span> @endif
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
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card"><span class="val">{{ $totalDays }}</span><span class="lbl">Total Days</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #3b82f6;"><span class="val">{{ $workingDays }}</span><span class="lbl">Working Days</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #10b981;"><span class="val">{{ $presentDays }}</span><span class="lbl">Present</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.4mm;">
                <div class="stat-card" style="border-top: 1mm solid #ef4444;"><span class="val">{{ $absentDays }}</span><span class="lbl">Absent</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #6366f1;"><span class="val">{{ $leaveDays }}</span><span class="lbl">Leave</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #6b7280;"><span class="val">{{ $weekoffDays }}</span><span class="lbl">Week Offs</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #f59e0b;"><span class="val">{{ $holidayDays }}</span><span class="lbl">Holidays</span></div>
            </td>
        </tr>
        <tr>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #06b6d4;"><span class="val">{{ $halfDays }}</span><span class="lbl">Half Days</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #86198f;"><span class="val">{{ $onDutyDays }}</span><span class="lbl">On Duty</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #0ea5e9;"><span class="val">{{ $shortLeaves }}</span><span class="lbl">Short LV</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #ec4899;"><span class="val">{{ $totalOT }}h</span><span class="lbl">Overtime</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #dc2626;"><span class="val" style="color: #dc2626;">{{ $totalLate }}m</span><span class="lbl">Late Min.</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="border-top: 1mm solid #dc2626;"><span class="val" style="color: #dc2626;">{{ $totalEarly }}m</span><span class="lbl">Early Min.</span></div>
            </td>
            <td style="width: 14.28%; padding: 0.5mm;">
                <div class="stat-card" style="background: #f9fafb; border: 1px solid #1e3a5f;"><span class="val" style="color: #111827;">{{ $totalLop }}</span><span class="lbl">Total LOP</span></div>
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <div class="signature-section">
        <table style="width: 100%;" class="table-borderless">
            <tr>
                <td style="width: 30%; border-top: 0.2mm dashed #9ca3af; padding-top: 2mm; text-align: center;">
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
