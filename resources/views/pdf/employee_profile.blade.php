<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $employee->first_name }} | Employee Profile Portfolio</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
        }
        .profile-photo-container {
            width: 95px;
            vertical-align: top;
        }
        .profile-photo {
            width: 85px;
            height: 85px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .profile-photo-placeholder {
            width: 85px;
            height: 85px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-align: center;
            line-height: 85px;
            color: #94a3b8;
            font-weight: bold;
            font-size: 18px;
        }
        .header-info {
            vertical-align: top;
            padding-left: 20px;
        }
        .employee-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 5px 0;
        }
        .employee-code-badge {
            background-color: #e0e7ff;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 100px;
            font-weight: bold;
            font-size: 9px;
            display: inline-block;
            margin-bottom: 5px;
        }
        .header-meta-table {
            width: 100%;
            margin-top: 5px;
        }
        .header-meta-table td {
            padding: 2px 0;
            font-size: 10px;
            color: #475569;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            background-color: #eff6ff;
            padding: 4px 8px;
            margin-top: 15px;
            margin-bottom: 6px;
            border-left: 3px solid #3b82f6;
            text-transform: uppercase;
        }
        .key-value-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .key-value-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .key-value-label {
            font-weight: bold;
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            width: 15%;
        }
        .key-value-value {
            color: #1e293b;
            font-weight: 600;
            width: 35%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .data-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .badge-secondary {
            background-color: #f1f5f9;
            color: #475569;
        }
        .page-break {
            page-break-after: always;
        }
        .document-wrapper {
            page-break-inside: avoid;
            margin-bottom: 25px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #ffffff;
            text-align: center;
        }
        .document-header {
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
            margin-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
            text-align: left;
        }
        .document-image {
            max-width: 100%;
            max-height: 620px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 3px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Top Identity Section -->
    <table class="header-table">
        <tr>
            <td class="profile-photo-container">
                @if($employee->employee_photo && file_exists(public_path('storage' . $employee->employee_photo->media)))
                    <img src="{{ public_path('storage' . $employee->employee_photo->media) }}" class="profile-photo" alt="Profile Photo">
                @else
                    <div class="profile-photo-placeholder">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                @endif
            </td>
            <td class="header-info">
                <div class="employee-name">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</div>
                <div class="employee-code-badge">ID: {{ $employee->employee_code }}</div>
                
                <table class="header-meta-table">
                    <tr>
                        <td style="width: 50%;"><strong>Designation:</strong> {{ $employee->employee_designation ? $employee->employee_designation->designation->designation : '—' }}</td>
                        <td style="width: 50%;"><strong>Department:</strong> {{ $employee->employee_department ? $employee->employee_department->department->department : '—' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Official Email:</strong> {{ $employee->email ?: '—' }}</td>
                        <td><strong>Mobile Number:</strong> {{ $employee->phone ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date of Joining:</strong> {{ $employee->doj ?: '—' }}</td>
                        <td>
                            <strong>Employment Status:</strong> 
                            @if($employee->doe)
                                <span style="color: #b91c1c; font-weight: bold;">Exited ({{ $employee->doe }})</span>
                            @else
                                <span style="color: #15803d; font-weight: bold;">Active / In-Service</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bio-Data & Personal Parameters -->
    <div class="section-title">Bio-Data & Personal Profile</div>
    <table class="key-value-table">
        <tr>
            <td class="key-value-label">Date of Birth</td>
            <td class="key-value-value">{{ $employee->dob ?: '—' }}</td>
            <td class="key-value-label">Gender</td>
            <td class="key-value-value">{{ $employee->gender ?: '—' }}</td>
        </tr>
        <tr>
            <td class="key-value-label">Blood Group</td>
            <td class="key-value-value" style="color: #b91c1c;">{{ $employee->blood_group ?: '—' }}</td>
            <td class="key-value-label">Nationality</td>
            <td class="key-value-value">{{ $employee->nationality ?: '—' }}</td>
        </tr>
        <tr>
            <td class="key-value-label">Religion</td>
            <td class="key-value-value">{{ $employee->religion ?: '—' }}</td>
            <td class="key-value-label">Marital Status</td>
            <td class="key-value-value">{{ $employee->marital_status ?: '—' }}</td>
        </tr>
        <tr>
            <td class="key-value-label">Caste / Sub-caste</td>
            <td class="key-value-value">{{ $employee->cast ?: '—' }} @if($employee->subcast) ({{ $employee->subcast }}) @endif</td>
            <td class="key-value-label">Mother Tongue</td>
            <td class="key-value-value">{{ $employee->mothertongue ?: '—' }}</td>
        </tr>
        <tr>
            <td class="key-value-label">Qualification</td>
            <td class="key-value-value">{{ $employee->qualification ?: '—' }}</td>
            <td class="key-value-label">Degree</td>
            <td class="key-value-value">{{ $employee->degree ?: '—' }}</td>
        </tr>
    </table>

    <!-- Compliance Vault -->
    <div class="section-title">Compliance Parameters</div>
    <table class="key-value-table">
        <tr>
            <td class="key-value-label">Aadhar Card</td>
            <td class="key-value-value">{{ $employee->aadhar ?: '—' }}</td>
            <td class="key-value-label">PAN Number</td>
            <td class="key-value-value" style="text-transform: uppercase;">{{ $employee->pan ?: '—' }}</td>
        </tr>
        <tr>
            <td class="key-value-label">PF Account</td>
            <td class="key-value-value">{{ $employee->pf ?: '—' }} @if($employee->old_pf) (Old: {{ $employee->old_pf }}) @endif</td>
            <td class="key-value-label">UAN Number</td>
            <td class="key-value-value">{{ $employee->uan ?: '—' }} @if($employee->old_uan) (Old: {{ $employee->old_uan }}) @endif</td>
        </tr>
        <tr>
            <td class="key-value-label">ESIC ID</td>
            <td class="key-value-value">{{ $employee->esic ?: '—' }} @if($employee->old_esic) (Old: {{ $employee->old_esic }}) @endif</td>
            <td class="key-value-label">Biometric Tag ID</td>
            <td class="key-value-value">{{ $employee->tagid ?: '—' }}</td>
        </tr>
    </table>

    <!-- Residence Details -->
    <div class="section-title">Residence (Addresses)</div>
    @if($addresses->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No registered residential addresses.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Street & Landmark</th>
                    <th style="width: 20%;">City</th>
                    <th style="width: 10%;">Pincode</th>
                    <th style="width: 15%;">State/Region</th>
                    <th style="width: 10%;">Country</th>
                </tr>
            </thead>
            <tbody>
                @foreach($addresses as $address)
                    <tr>
                        <td><strong>{{ $address->address }}</strong></td>
                        <td>{{ $address->city }}</td>
                        <td style="font-family: monospace;">{{ $address->pincode }}</td>
                        <td>{{ $address->state }}</td>
                        <td>{{ $address->country }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Bank Details -->
    <div class="section-title">Bank Details</div>
    @if($banks->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No registered bank account records.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Bank Name</th>
                    <th style="width: 25%;">Account Holder Name</th>
                    <th style="width: 25%;">Account Number</th>
                    <th style="width: 20%;">IFSC Code</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banks as $bank)
                    <tr>
                        <td><strong>{{ $bank->bank_name }}</strong>@if($bank->branch) ({{ $bank->branch }}) @endif</td>
                        <td>{{ $bank->account_holder_name ?: '—' }}</td>
                        <td style="font-family: monospace;">{{ $bank->account_no }}</td>
                        <td style="font-family: monospace; text-transform: uppercase;">{{ $bank->ifsc }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Work Location chronology -->
    <div class="section-title">Work Location Timeline</div>
    @if($work_locations->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No historical site placements recorded.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Assigned Site Location</th>
                    <th style="width: 20%;">Start Date</th>
                    <th style="width: 20%;">End Date</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($work_locations as $wl)
                    <tr>
                        <td><strong>{{ $wl->work_location ? $wl->work_location->location_name : 'Unknown Site' }}</strong></td>
                        <td>{{ $wl->from }}</td>
                        <td>{{ $wl->to ?: 'Present' }}</td>
                        <td style="text-align: center;">
                            @if(is_null($wl->to))
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Ended</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Department Timeline -->
    <div class="section-title">Department Assignments</div>
    @if($departments->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No historical department assignments recorded.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Department Name</th>
                    <th style="width: 20%;">Start Date</th>
                    <th style="width: 20%;">End Date</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $dept)
                    <tr>
                        <td><strong>{{ $dept->department ? $dept->department->department : 'Unknown Department' }}</strong></td>
                        <td>{{ $dept->from }}</td>
                        <td>{{ $dept->to ?: 'Present' }}</td>
                        <td style="text-align: center;">
                            @if(is_null($dept->to))
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Ended</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="page-break"></div>

    <!-- Designation Timeline -->
    <div class="section-title">Designation History</div>
    @if($designations->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No historical designation changes recorded.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Designation Title</th>
                    <th style="width: 20%;">Start Date</th>
                    <th style="width: 20%;">End Date</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($designations as $desg)
                    <tr>
                        <td><strong>{{ $desg->designation ? $desg->designation->designation : 'Unknown Designation' }}</strong></td>
                        <td>{{ $desg->from }}</td>
                        <td>{{ $desg->to ?: 'Present' }}</td>
                        <td style="text-align: center;">
                            @if(is_null($desg->to))
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Ended</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Leave Policy History -->
    <div class="section-title">Leave Policy (Leave Groups)</div>
    @if($leave_groups->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No historical leave policies assigned.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Leave Policy / Group Name</th>
                    <th style="width: 20%;">Start Date</th>
                    <th style="width: 20%;">End Date</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leave_groups as $lg)
                    <tr>
                        <td><strong>{{ $lg->leave_group ? $lg->leave_group->name : 'Unknown Policy' }}</strong></td>
                        <td>{{ $lg->from }}</td>
                        <td>{{ $lg->to ?: 'Present' }}</td>
                        <td style="text-align: center;">
                            @if(is_null($lg->to) || strtotime($lg->to) >= time())
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Ended</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Payroll & CTC History -->
    <div class="section-title">Payroll & CTC History</div>
    @if($salaries->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No historical payroll brackets structured.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Salary Group Structure</th>
                    <th style="width: 15%;">Effective From</th>
                    <th style="width: 15%;">Annual CTC</th>
                    <th style="width: 15%;">Monthly Gross</th>
                    <th style="width: 15%;">Net Pay</th>
                    <th style="width: 15%;">Structure Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salaries as $sal)
                    <tr>
                        <td><strong>{{ $sal->salary_group ? $sal->salary_group->salary_group_name : 'Custom Structure' }}</strong></td>
                        <td>{{ $sal->effective_from }}</td>
                        <td><strong>{{ number_format($sal->ctc, 2) }}</strong></td>
                        <td>{{ number_format($sal->gross_pay, 2) }}</td>
                        <td>{{ number_format($sal->net_pay, 2) }}</td>
                        <td style="font-size: 8px; color: #64748b;">{{ $sal->note ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Core Services -->
    <div class="section-title">Core Services</div>
    @if($services->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No core services assigned.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Service Component Name</th>
                    <th style="width: 20%;">Start Date</th>
                    <th style="width: 20%;">End Date</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $srv)
                    <tr>
                        <td><strong>{{ $srv->services_component ? $srv->services_component->name : 'Unknown Service' }}</strong></td>
                        <td>{{ $srv->from }}</td>
                        <td>{{ $srv->to ?: 'Present' }}</td>
                        <td style="text-align: center;">
                            @if(is_null($srv->to))
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Ended</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Educational Docs -->
    <div class="section-title">Educational Profile (Credentials)</div>
    @if($educations->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No educational credentials archived.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Course / Degree</th>
                    <th style="width: 35%;">Board / University</th>
                    <th style="width: 12%;">Passing Year</th>
                    <th style="width: 10%;">Result</th>
                    <th style="width: 13%;">Aggregate Marks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($educations as $edu)
                    <tr>
                        <td><strong>{{ $edu->course }}</strong></td>
                        <td>{{ $edu->board_university }}</td>
                        <td style="font-family: monospace;">{{ $edu->year }}</td>
                        <td>
                            @if(strtolower($edu->result) === 'pass')
                                <span style="color: #15803d; font-weight: bold;">{{ $edu->result }}</span>
                            @else
                                <span style="color: #b91c1c; font-weight: bold;">{{ $edu->result }}</span>
                            @endif
                        </td>
                        <td><strong>{{ $edu->aggregate }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Document Vault -->
    <div class="section-title">Document Vault Inventory</div>
    @if($documents->isEmpty())
        <p style="color: #94a3b8; font-style: italic; margin-left: 10px;">No compliance documents uploaded.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Document Name / Type</th>
                    <th style="width: 45%;">Stored Filename</th>
                    <th style="width: 15%;">Registered Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                    <tr>
                        <td><strong>{{ $doc->document_name }}</strong></td>
                        <td style="font-family: monospace; font-size: 8px; color: #64748b;">{{ basename($doc->document) }}</td>
                        <td>{{ $doc->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Document Attachments Section (Render Images) -->
    @php
        // Filter documents and education files that are images
        $imageAttachments = [];
        
        foreach($documents as $d) {
            $ext = strtolower(pathinfo($d->document, PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imageAttachments[] = [
                    'title' => $d->document_name,
                    'path' => $d->document
                ];
            }
        }
        
        foreach($educations as $e) {
            $ext = strtolower(pathinfo($e->document, PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imageAttachments[] = [
                    'title' => 'Educational Credential: ' . $e->course,
                    'path' => $e->document
                ];
            }
        }
    @endphp

    @if(count($imageAttachments) > 0)
        <div class="page-break"></div>
        <div class="section-title" style="background-color: #fef3c7; border-left-color: #d97706; color: #92400e;">Attached Documents</div>
        <p style="color: #64748b; font-style: italic; font-size: 9px; margin-bottom: 15px; margin-left: 10px;">Rendering all registered image credentials in local attachments archive.</p>
        
        @foreach($imageAttachments as $att)
            @if(!$loop->first)
                <div class="page-break"></div>
            @endif
            <div class="document-wrapper">
                <div class="document-header">{{ $att['title'] }}</div>
                @if(file_exists(public_path('storage' . $att['path'])))
                    <img src="{{ public_path('storage' . $att['path']) }}" class="document-image" alt="{{ $att['title'] }}">
                @else
                    <p style="color: #94a3b8; font-style: italic; font-size: 9px;">Attachment file could not be located on server local disk.</p>
                @endif
            </div>
        @endforeach
    @endif

</body>
</html>
