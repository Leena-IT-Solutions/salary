<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Employee Enrollment Application Form - {{ $company->company_name ?? 'Sarvodaya Vidyalay' }}</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .text-muted { color: #64748b; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* Organization Header section */
        .org-header-table {
            width: 100%;
            margin-bottom: 6px;
            border-bottom: 2.5px solid #2563eb;
            padding-bottom: 6px;
        }
        .org-logo {
            max-height: 54px;
            max-width: 95px;
        }
        .org-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }
        .org-meta {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
        .form-title-banner {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 10.5pt;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            margin: 6px 0 3px 0;
            letter-spacing: 1px;
            border-radius: 3px;
        }
        .form-subtitle {
            font-size: 7.5pt;
            color: #64748b;
            font-style: italic;
            text-align: center;
            margin-bottom: 8px;
        }

        /* Section Styling */
        .section-header {
            background-color: #eff6ff;
            color: #1e40af;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 4px 8px;
            border-left: 3.5px solid #2563eb;
            border-top: 1px solid #dbeafe;
            border-right: 1px solid #dbeafe;
            border-bottom: 1px solid #dbeafe;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 6px;
            border-radius: 2px;
        }

        /* Tables & Forms */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .form-table td {
            padding: 5.5px 6px;
            vertical-align: middle;
            font-size: 8.5pt;
        }
        .field-label {
            font-weight: bold;
            color: #334155;
            font-size: 8pt;
            white-space: nowrap;
        }
        .field-line {
            border-bottom: 1px dashed #94a3b8;
            height: 18px;
            display: block;
            width: 100%;
        }

        /* Data grids (education) */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 8px 0;
        }
        .grid-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            padding: 6px 4px;
            text-align: center;
        }
        .grid-table td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            font-size: 8pt;
            height: 24px;
        }

        /* Office Box */
        .office-box {
            border: 1px solid #93c5fd;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        .office-box-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed #bfdbfe;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        /* Photo Box */
        .photo-box {
            width: 95px;
            height: 115px;
            border: 1.5px dashed #64748b;
            background-color: #fafafa;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
            color: #64748b;
            padding: 6px;
            box-sizing: border-box;
            border-radius: 4px;
        }

        /* Checkboxes */
        .checkbox-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1.2px solid #334155;
            margin-right: 3px;
            vertical-align: middle;
            background-color: #ffffff;
            border-radius: 1px;
        }
        
        /* Empty write-in boxes (DOB, Phone, Aadhaar, PAN, IFSC) */
        .char-box {
            display: inline-block;
            width: 12px;
            height: 16px;
            border: 1.2px solid #64748b;
            margin-right: 1.5px;
            vertical-align: middle;
            background-color: #ffffff;
            border-radius: 1px;
        }

        .page-break {
            page-break-after: always;
        }
        
        .declaration-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 7.5pt;
            color: #334155;
            text-align: justify;
            line-height: 1.4;
            border-radius: 3px;
            margin: 6px 0 10px 0;
        }

        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-line {
            border-top: 1.2px solid #334155;
            margin-top: 35px;
            padding-top: 4px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            color: #1e293b;
        }
    </style>
</head>
<body>

    @php
        $logoPath = null;
        if ($company && $company->logo && file_exists(public_path($company->logo))) {
            $logoPath = public_path($company->logo);
        } elseif ($company && $company->logo && file_exists(public_path('storage/logo/' . $company->logo))) {
            $logoPath = public_path('storage/logo/' . $company->logo);
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoPath = public_path('images/logo.png');
        }
    @endphp

    <!-- ============================================================== -->
    <!-- PAGE 1: PRIMARY IDENTITY, CONTACT, ADDRESS & EMERGENCY          -->
    <!-- ============================================================== -->

    <!-- Header Table -->
    <table class="org-header-table">
        <tr>
            <td style="width: 12%; text-align: center; vertical-align: middle;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" class="org-logo" alt="Logo">
                @else
                    <div style="font-size: 24pt; font-weight: bold; color: #1e3a8a;">SV</div>
                @endif
            </td>
            <td style="width: 70%; text-align: center; vertical-align: middle; padding: 0 10px;">
                <div class="org-name">{{ $company->company_name ?? 'Sarvodaya Vidyalay' }}</div>
                <div class="org-meta">
                    {{ $company->address ?? 'Jambhul Road, Sarvodya Nagar' }}, 
                    {{ $company->city ?? 'Ambernath' }} - {{ $company->pincode ?? '421505' }}, {{ $company->state ?? 'Maharashtra' }}
                </div>
                <div class="org-meta" style="margin-top: 3px;">
                    <strong>Phone:</strong> {{ $company->phone ?? '9112021959' }} &nbsp;|&nbsp; 
                    <strong>Email:</strong> {{ $company->email ?? 'sarvodayavidyalayschool@gmail.com' }}
                </div>
            </td>
            <td style="width: 18%; text-align: right; vertical-align: top;">
                <table align="right">
                    <tr>
                        <td class="photo-box">
                            AFFIX RECENT<br>PASSPORT SIZE<br>PHOTOGRAPH<br>HERE<br>
                            <span style="font-size: 6pt; color: #94a3b8; display: inline-block; margin-top: 4px;">(Self-Attested)</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="form-title-banner">STAFF / EMPLOYEE ENROLLMENT APPLICATION FORM</div>
    <div class="form-subtitle">Please fill all details clearly in BLOCK CAPITAL LETTERS using black or blue ink pen.</div>

    <!-- Office Administrative Record Box -->
    <div class="office-box">
        <div class="office-box-title">FOR OFFICE / ADMINISTRATIVE USE ONLY</div>
        <table style="width: 100%; font-size: 8pt;">
            <tr>
                <td style="width: 18%;"><strong>Staff / Emp Code:</strong></td>
                <td style="width: 32%;"><span class="field-line"></span></td>
                <td style="width: 18%;"><strong>Biometric Tag ID:</strong></td>
                <td style="width: 32%;"><span class="field-line"></span></td>
            </tr>
            <tr>
                <td><strong>Date of Joining:</strong></td>
                <td><span class="field-line"></span></td>
                <td><strong>Work Location / Site:</strong></td>
                <td><span class="field-line"></span></td>
            </tr>
            <tr>
                <td><strong>Assigned Department:</strong></td>
                <td><span class="field-line"></span></td>
                <td><strong>Designation / Post:</strong></td>
                <td><span class="field-line"></span></td>
            </tr>
        </table>
    </div>

    <!-- Section 1: Basic Identity & Demographics -->
    <div class="section-header">1. Personal Details & Demographics</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 18%;">Full Legal Name:</td>
            <td colspan="3"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Surname:</td>
            <td style="width: 32%;"><span class="field-line"></span></td>
            <td class="field-label" style="width: 18%;">First Name:</td>
            <td style="width: 32%;"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Middle / Father's Name:</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Mother's Name:</td>
            <td><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Date of Birth:</td>
            <td>
                <!-- Clean empty boxes with sub-label below -->
                <div style="display: inline-block; vertical-align: middle;">
                    <span class="char-box"></span><span class="char-box"></span>
                    <span style="font-weight: bold; margin: 0 1px;">/</span>
                    <span class="char-box"></span><span class="char-box"></span>
                    <span style="font-weight: bold; margin: 0 1px;">/</span>
                    <span class="char-box"></span><span class="char-box"></span><span class="char-box"></span><span class="char-box"></span>
                    <div style="font-size: 6pt; color: #64748b; margin-top: 1px;">(DD / MM / YYYY)</div>
                </div>
            </td>
            <td class="field-label">Gender Identity:</td>
            <td>
                <span class="checkbox-box"></span> Male &nbsp;&nbsp;
                <span class="checkbox-box"></span> Female &nbsp;&nbsp;
                <span class="checkbox-box"></span> Other
            </td>
        </tr>
        <tr>
            <td class="field-label">Blood Group:</td>
            <td>
                <span class="checkbox-box"></span> O+ &nbsp;
                <span class="checkbox-box"></span> O- &nbsp;
                <span class="checkbox-box"></span> A+ &nbsp;
                <span class="checkbox-box"></span> A- &nbsp;
                <span class="checkbox-box"></span> B+ &nbsp;
                <span class="checkbox-box"></span> B- &nbsp;
                <span class="checkbox-box"></span> AB+ &nbsp;
                <span class="checkbox-box"></span> AB-
            </td>
            <td class="field-label">Marital Status:</td>
            <td>
                <span class="checkbox-box"></span> Married &nbsp;&nbsp;
                <span class="checkbox-box"></span> Single &nbsp;&nbsp;
                <span class="checkbox-box"></span> Other
            </td>
        </tr>
        <tr>
            <td class="field-label">Nationality:</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Mother Tongue:</td>
            <td><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Religion:</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Caste / Sub-Caste:</td>
            <td><span class="field-line"></span></td>
        </tr>
    </table>

    <!-- Section 2: Contact Details -->
    <div class="section-header">2. Communication & Contact Channels</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 18%;">Primary Mobile No:</td>
            <td style="width: 32%; white-space: nowrap;">
                @for($i=0; $i<10; $i++)
                    <span class="char-box"></span>
                @endfor
            </td>
            <td class="field-label" style="width: 18%;">Alternate / Tel No:</td>
            <td style="width: 32%;"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Email Address:</td>
            <td colspan="3"><span class="field-line"></span></td>
        </tr>
    </table>

    <!-- Section 3: Residential Address -->
    <div class="section-header">3. Residential Address Particulars</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 18%; vertical-align: top;">Present Address:<br><span style="font-size: 6.5pt; color: #64748b; font-weight: normal;">(Current Residence)</span></td>
            <td colspan="3">
                <span class="field-line" style="margin-bottom: 7px;"></span>
                <span class="field-line" style="margin-bottom: 7px;"></span>
                <table style="width: 100%; margin-top: 3px;">
                    <tr>
                        <td style="width: 33%; padding: 0;"><strong>City:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 33%; padding: 0;"><strong>State:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 34%; padding: 0; white-space: nowrap;"><strong>Pincode:</strong> 
                            @for($i=0; $i<6; $i++)
                                <span class="char-box"></span>
                            @endfor
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="field-label" style="width: 18%; vertical-align: top; padding-top: 8px;">Permanent Address:</td>
            <td colspan="3" style="padding-top: 8px;">
                <div style="font-size: 7.5pt; margin-bottom: 5px;">
                    <span class="checkbox-box"></span> <strong>Same as Present Address</strong> (Check if identical)
                </div>
                <span class="field-line" style="margin-bottom: 7px;"></span>
                <span class="field-line" style="margin-bottom: 7px;"></span>
                <table style="width: 100%; margin-top: 3px;">
                    <tr>
                        <td style="width: 33%; padding: 0;"><strong>City:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 33%; padding: 0;"><strong>State:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 34%; padding: 0; white-space: nowrap;"><strong>Pincode:</strong> 
                            @for($i=0; $i<6; $i++)
                                <span class="char-box"></span>
                            @endfor
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Section 4: Emergency Reference (Placed on Page 1 to balance page height perfectly!) -->
    <div class="section-header">4. Emergency Contact & Family Reference</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 20%;">Emergency Contact Name:</td>
            <td style="width: 30%;"><span class="field-line"></span></td>
            <td class="field-label" style="width: 18%;">Relationship:</td>
            <td style="width: 32%;"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Emergency Phone Number:</td>
            <td style="white-space: nowrap;">
                @for($i=0; $i<10; $i++)
                    <span class="char-box"></span>
                @endfor
            </td>
            <td class="field-label">Alternate Number:</td>
            <td><span class="field-line"></span></td>
        </tr>
    </table>

    <div style="text-align: right; font-size: 7pt; color: #94a3b8; margin-top: 15px;">
        Page 1 of 2 &nbsp;&bull;&nbsp; {{ $company->company_name ?? 'Sarvodaya Vidyalay' }} Employee Enrollment Form
    </div>

    <!-- ============================================================== -->
    <!-- PAGE 2: EDUCATION, STATUTORY (KYC), BANKING & SIGNATURES       -->
    <!-- ============================================================== -->
    <div class="page-break"></div>

    <!-- Section 5: Educational Credentials -->
    <div class="section-header" style="margin-top: 0;">5. Educational & Professional Qualifications</div>
    <table class="form-table" style="margin-bottom: 2px;">
        <tr>
            <td class="field-label" style="width: 20%;">Highest Qualification:</td>
            <td colspan="3">
                <span class="checkbox-box"></span> SSC/Primary &nbsp;&nbsp;
                <span class="checkbox-box"></span> HSC/12th &nbsp;&nbsp;
                <span class="checkbox-box"></span> Graduate &nbsp;&nbsp;
                <span class="checkbox-box"></span> Post Graduate &nbsp;&nbsp;
                <span class="checkbox-box"></span> Doctorate/PhD &nbsp;&nbsp;
                <span class="checkbox-box"></span> Other
            </td>
        </tr>
        <tr>
            <td class="field-label">Specialization / Degree:</td>
            <td colspan="3"><span class="field-line"></span></td>
        </tr>
    </table>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 22%;">Examination / Degree</th>
                <th style="width: 38%;">Board / University / Institute</th>
                <th style="width: 12%;">Passing Year</th>
                <th style="width: 14%;">Marks / CGPA</th>
                <th style="width: 14%;">Grade / Div</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>10th / SSC</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>12th / HSC / Diploma</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>Graduation (Degree)</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>Post-Graduation / B.Ed</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>Other / Technical</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Section 6: Statutory & Identity Proofs (KYC) -->
    <div class="section-header">6. Statutory Identification & Social Security (KYC)</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 22%;">Aadhaar Card Number:</td>
            <td style="width: 28%; white-space: nowrap;">
                @for($i=0; $i<4; $i++) <span class="char-box"></span> @endfor - 
                @for($i=0; $i<4; $i++) <span class="char-box"></span> @endfor - 
                @for($i=0; $i<4; $i++) <span class="char-box"></span> @endfor
            </td>
            <td class="field-label" style="width: 20%;">PAN Card Number:</td>
            <td style="width: 30%; white-space: nowrap;">
                @for($i=0; $i<10; $i++)
                    <span class="char-box"></span>
                @endfor
            </td>
        </tr>
        <tr>
            <td class="field-label">UAN (Provident Fund):</td>
            <td style="white-space: nowrap;">
                @for($i=0; $i<12; $i++)
                    <span class="char-box"></span>
                @endfor
            </td>
            <td class="field-label">Previous UAN (If any):</td>
            <td><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">PF Registration No:</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Previous PF No (If any):</td>
            <td><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">ESIC Number (If any):</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Previous ESIC No:</td>
            <td><span class="field-line"></span></td>
        </tr>
    </table>

    <!-- Section 7: Bank Remittance Details -->
    <div class="section-header">7. Bank Details for Salary Credit Remittance</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 22%;">Account Holder Name:</td>
            <td colspan="3"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Bank Name:</td>
            <td style="width: 28%;"><span class="field-line"></span></td>
            <td class="field-label" style="width: 20%;">Branch Location:</td>
            <td style="width: 30%;"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Bank Account Number:</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Account Type:</td>
            <td>
                <span class="checkbox-box"></span> Salary &nbsp;&nbsp;
                <span class="checkbox-box"></span> Savings &nbsp;&nbsp;
                <span class="checkbox-box"></span> Current
            </td>
        </tr>
        <tr>
            <td class="field-label">IFSC Code:</td>
            <td colspan="3" style="white-space: nowrap;">
                @for($i=0; $i<11; $i++)
                    <span class="char-box"></span>
                @endfor
                <span style="font-size: 7pt; color: #64748b; margin-left: 8px;">(11-digit alphanumeric code as per chequebook/passbook)</span>
            </td>
        </tr>
    </table>

    <!-- Section 8: Document Enclosures Checklist -->
    <div class="section-header">8. Mandatory Enclosures / Attachments Checklist</div>
    <table style="width: 100%; font-size: 7.5pt; margin-bottom: 6px;">
        <tr>
            <td style="width: 50%; padding: 4px 6px;">
                <span class="checkbox-box"></span> Copy of Aadhaar Card (Self-Attested)<br>
                <div style="height: 4px;"></div>
                <span class="checkbox-box"></span> Copy of PAN Card (Self-Attested)<br>
                <div style="height: 4px;"></div>
                <span class="checkbox-box"></span> Cancelled Cheque / Bank Passbook Copy
            </td>
            <td style="width: 50%; padding: 4px 6px;">
                <span class="checkbox-box"></span> 2 Recent Passport Size Photographs<br>
                <div style="height: 4px;"></div>
                <span class="checkbox-box"></span> Educational Certificates & Degree Marksheets<br>
                <div style="height: 4px;"></div>
                <span class="checkbox-box"></span> Previous Relieving / Experience Letter (If applicable)
            </td>
        </tr>
    </table>

    <!-- Section 9: Self Declaration & Signatures -->
    <div class="section-header">9. Candidate Self-Declaration & Signatures</div>
    <div class="declaration-box">
        I hereby declare and affirm that all the statements, particulars, and information furnished above in this enrollment application form are true, complete, and correct to the best of my knowledge and belief. In the event of any information being found false, misleading, fabricated, or incorrect at any stage, my candidature / employment shall be subject to immediate cancellation and termination without notice.
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 30%;">
                <div style="font-size: 8pt; margin-bottom: 8px;"><strong>Date:</strong> _____ / _____ / 202___</div>
                <div style="font-size: 8pt;"><strong>Place:</strong> ___________________</div>
            </td>
            <td style="width: 35%;">
                <div class="sig-line">
                    Verified By (HR / Admin)<br>
                    <span style="font-size: 6.5pt; font-weight: normal; color: #64748b;">Signature & Date</span>
                </div>
            </td>
            <td style="width: 35%;">
                <div class="sig-line">
                    Signature of Candidate / Applicant<br>
                    <span style="font-size: 6.5pt; font-weight: normal; color: #64748b;">(Sign inside this space)</span>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 18px; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 62%; font-size: 7.5pt; color: #64748b; vertical-align: middle;">
                    Form Approved for Registration in Portal by: __________________________________
                </td>
                <td style="width: 38%; text-align: center; vertical-align: middle;">
                    <div style="border: 1.2px solid #cbd5e1; padding: 14px 8px; font-size: 7.5pt; color: #64748b; border-radius: 4px; background-color: #fafafa;">
                        Principal / Authorized Signatory & Official Stamp
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: right; font-size: 7pt; color: #94a3b8; margin-top: 12px;">
        Page 2 of 2 &nbsp;&bull;&nbsp; {{ $company->company_name ?? 'Sarvodaya Vidyalay' }} Employee Enrollment Form
    </div>

</body>
</html>
