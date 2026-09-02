<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Employee Enrollment Application Form - {{ $company->company_name ?? 'Sarvodaya Vidyalay' }}</title>
    <style>
        @page {
            margin: 8mm 10mm 8mm 10mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.25;
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
        
        /* Header section */
        .org-header-table {
            width: 100%;
            margin-bottom: 4px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 4px;
        }
        .org-logo {
            max-height: 48px;
            max-width: 90px;
        }
        .org-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .org-meta {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }
        .form-title-banner {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            padding: 3px 0;
            margin: 5px 0 3px 0;
            letter-spacing: 1px;
            border-radius: 2px;
        }
        .form-subtitle {
            font-size: 7pt;
            color: #64748b;
            font-style: italic;
            text-align: center;
            margin-bottom: 5px;
        }

        /* Section Styling */
        .section-header {
            background-color: #eff6ff;
            color: #1e40af;
            font-size: 8pt;
            font-weight: bold;
            padding: 2.5px 6px;
            border-left: 3px solid #2563eb;
            border-top: 1px solid #dbeafe;
            border-right: 1px solid #dbeafe;
            border-bottom: 1px solid #dbeafe;
            text-transform: uppercase;
            margin-top: 5px;
            margin-bottom: 3px;
        }

        /* Tables & Forms */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .form-table td {
            padding: 3px 5px;
            vertical-align: middle;
            font-size: 8pt;
        }
        .field-label {
            font-weight: bold;
            color: #334155;
            font-size: 7.5pt;
            white-space: nowrap;
        }
        .field-line {
            border-bottom: 1px dotted #94a3b8;
            height: 14px;
            display: block;
        }
        .field-box {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            height: 16px;
            border-radius: 2px;
        }

        /* Data grids (e.g. education) */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 5px 0;
        }
        .grid-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 7.5pt;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            padding: 3px 4px;
            text-align: center;
        }
        .grid-table td {
            border: 1px solid #cbd5e1;
            padding: 4px;
            font-size: 7.5pt;
            height: 16px;
        }

        /* Office Box */
        .office-box {
            border: 1px solid #93c5fd;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 4px;
        }
        .office-box-title {
            font-size: 7pt;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed #bfdbfe;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }

        /* Photo Box */
        .photo-box {
            width: 90px;
            height: 105px;
            border: 1px dashed #64748b;
            background-color: #fafafa;
            text-align: center;
            vertical-align: middle;
            font-size: 6.5pt;
            color: #94a3b8;
            padding: 4px;
            box-sizing: border-box;
        }

        /* Checkboxes */
        .checkbox-box {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #475569;
            margin-right: 3px;
            vertical-align: middle;
        }
        .char-box {
            display: inline-block;
            width: 9.5px;
            height: 12px;
            border: 1px solid #94a3b8;
            margin-right: 1px;
            vertical-align: middle;
            text-align: center;
            font-size: 6.5pt;
        }

        .page-break {
            page-break-after: always;
        }
        
        .declaration-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 7pt;
            color: #334155;
            text-align: justify;
            line-height: 1.35;
            border-radius: 3px;
            margin: 4px 0 6px 0;
        }

        .signature-table {
            width: 100%;
            margin-top: 15px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 0 8px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            margin-top: 25px;
            padding-top: 3px;
            text-align: center;
            font-size: 7.5pt;
            font-weight: bold;
            color: #334155;
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
    <!-- PAGE 1: PRIMARY IDENTITY, DEMOGRAPHICS, ADDRESS & CONTACT       -->
    <!-- ============================================================== -->

    <!-- Header Table -->
    <table class="org-header-table">
        <tr>
            <td style="width: 12%; text-align: center; vertical-align: middle;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" class="org-logo" alt="Logo">
                @else
                    <div style="font-size: 20pt; font-weight: bold; color: #1e3a8a;">SV</div>
                @endif
            </td>
            <td style="width: 70%; text-align: center; vertical-align: middle; padding: 0 10px;">
                <div class="org-name">{{ $company->company_name ?? 'Sarvodaya Vidyalay' }}</div>
                <div class="org-meta">
                    {{ $company->address ?? 'Jambhul Road, Sarvodya Nagar' }}, 
                    {{ $company->city ?? 'Ambernath' }} - {{ $company->pincode ?? '421505' }}, {{ $company->state ?? 'Maharashtra' }}
                </div>
                <div class="org-meta">
                    <strong>Phone:</strong> {{ $company->phone ?? '9112021959' }} &nbsp;|&nbsp; 
                    <strong>Email:</strong> {{ $company->email ?? 'sarvodayavidyalayschool@gmail.com' }}
                </div>
            </td>
            <td style="width: 18%; text-align: right; vertical-align: top;">
                <table align="right">
                    <tr>
                        <td class="photo-box">
                            AFFIX RECENT<br>PASSPORT SIZE<br>PHOTOGRAPH<br>HERE<br>
                            <span style="font-size: 5.5pt; color: #94a3b8;">(Self-Attested)</span>
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
        <table style="width: 100%; font-size: 7.5pt;">
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
            <td class="field-label">Mother's Legal Name:</td>
            <td><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Date of Birth:</td>
            <td>
                <span class="char-box">D</span><span class="char-box">D</span> / 
                <span class="char-box">M</span><span class="char-box">M</span> / 
                <span class="char-box">Y</span><span class="char-box">Y</span><span class="char-box">Y</span><span class="char-box">Y</span>
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
                <span class="checkbox-box"></span> Married &nbsp;
                <span class="checkbox-box"></span> Single &nbsp;
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
            <td style="width: 32%;">
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
            <td class="field-label" style="width: 18%; vertical-align: top;">Present Address:<br><span style="font-size: 6.5pt; color: #64748b;">(Current Residence)</span></td>
            <td colspan="3">
                <span class="field-line" style="margin-bottom: 5px;"></span>
                <span class="field-line" style="margin-bottom: 5px;"></span>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 33%; padding: 0;"><strong>City:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 33%; padding: 0;"><strong>State:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 34%; padding: 0;"><strong>Pincode:</strong> 
                            @for($i=0; $i<6; $i++)
                                <span class="char-box"></span>
                            @endfor
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="field-label" style="width: 18%; vertical-align: top;">Permanent Address:</td>
            <td colspan="3">
                <div style="font-size: 7pt; margin-bottom: 3px;">
                    <span class="checkbox-box"></span> Same as Present Address (Check if identical)
                </div>
                <span class="field-line" style="margin-bottom: 5px;"></span>
                <span class="field-line" style="margin-bottom: 5px;"></span>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 33%; padding: 0;"><strong>City:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 33%; padding: 0;"><strong>State:</strong> <span class="field-line" style="display:inline-block; width: 70%;"></span></td>
                        <td style="width: 34%; padding: 0;"><strong>Pincode:</strong> 
                            @for($i=0; $i<6; $i++)
                                <span class="char-box"></span>
                            @endfor
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="text-align: right; font-size: 7pt; color: #94a3b8; margin-top: 15px;">
        Page 1 of 2 &nbsp;&bull;&nbsp; {{ $company->company_name ?? 'Sarvodaya Vidyalay' }} Employee Enrollment Form
    </div>

    <!-- ============================================================== -->
    <!-- PAGE 2: EDUCATION, STATUTORY (KYC), BANKING & SIGNATURES       -->
    <!-- ============================================================== -->
    <div class="page-break"></div>

    <!-- Section 4: Educational Credentials -->
    <div class="section-header" style="margin-top: 0;">4. Educational & Professional Qualifications</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 20%;">Highest Qualification:</td>
            <td colspan="3">
                <span class="checkbox-box"></span> SSC/Primary &nbsp;
                <span class="checkbox-box"></span> HSC/12th &nbsp;
                <span class="checkbox-box"></span> Graduate &nbsp;
                <span class="checkbox-box"></span> Post Graduate &nbsp;
                <span class="checkbox-box"></span> Doctorate/PhD &nbsp;
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
                <th style="width: 24%;">Examination / Degree</th>
                <th style="width: 36%;">Board / University / Institute</th>
                <th style="width: 12%;">Passing Year</th>
                <th style="width: 14%;">Percentage / CGPA</th>
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

    <!-- Section 5: Statutory & Identity Proofs (KYC) -->
    <div class="section-header">5. Statutory Identification & Social Security (KYC)</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 20%;">Aadhaar Card Number:</td>
            <td style="width: 30%; white-space: nowrap;">
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

    <!-- Section 6: Bank Remittance Details -->
    <div class="section-header">6. Bank Details for Salary Credit Remittance</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 22%;">Account Holder Name:</td>
            <td colspan="3"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Bank Name:</td>
            <td style="width: 28%;"><span class="field-line"></span></td>
            <td class="field-label" style="width: 22%;">Branch Location:</td>
            <td style="width: 28%;"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Bank Account Number:</td>
            <td><span class="field-line"></span></td>
            <td class="field-label">Account Type:</td>
            <td>
                <span class="checkbox-box"></span> Salary &nbsp;
                <span class="checkbox-box"></span> Savings &nbsp;
                <span class="checkbox-box"></span> Current
            </td>
        </tr>
        <tr>
            <td class="field-label">IFSC Code:</td>
            <td colspan="3">
                @for($i=0; $i<11; $i++)
                    <span class="char-box"></span>
                @endfor
                <span style="font-size: 7pt; color: #64748b; margin-left: 10px;">(11-digit alphanumeric code as per chequebook)</span>
            </td>
        </tr>
    </table>

    <!-- Section 7: Emergency Reference -->
    <div class="section-header">7. Emergency Contact & Family Reference</div>
    <table class="form-table">
        <tr>
            <td class="field-label" style="width: 22%;">Emergency Contact Person:</td>
            <td style="width: 28%;"><span class="field-line"></span></td>
            <td class="field-label" style="width: 22%;">Relationship:</td>
            <td style="width: 28%;"><span class="field-line"></span></td>
        </tr>
        <tr>
            <td class="field-label">Emergency Phone Number:</td>
            <td colspan="3"><span class="field-line"></span></td>
        </tr>
    </table>

    <!-- Section 8: Document Enclosures Checklist -->
    <div class="section-header">8. Mandatory Enclosures / Attachments Checklist</div>
    <table style="width: 100%; font-size: 7pt; margin-bottom: 4px;">
        <tr>
            <td style="width: 50%; padding: 2px 4px;">
                <span class="checkbox-box"></span> Copy of Aadhaar Card (Self-Attested)<br>
                <span class="checkbox-box"></span> Copy of PAN Card (Self-Attested)<br>
                <span class="checkbox-box"></span> Cancelled Cheque / Bank Passbook Copy
            </td>
            <td style="width: 50%; padding: 2px 4px;">
                <span class="checkbox-box"></span> 2 Recent Passport Size Photographs<br>
                <span class="checkbox-box"></span> Educational Certificates & Marksheets<br>
                <span class="checkbox-box"></span> Previous Relieving / Experience Letter (If applicable)
            </td>
        </tr>
    </table>

    <!-- Section 9: Self Declaration & Signatures -->
    <div class="section-header">9. Candidate Self-Declaration & Signatures</div>
    <div class="declaration-box">
        I hereby declare and affirm that all the statements and information furnished above in this application form are true, complete, and correct to the best of my knowledge and belief. In the event of any information being found false, fabricated, or incorrect at any stage, my candidature / employment shall be subject to immediate cancellation and termination without notice.
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 30%;">
                <div style="font-size: 7.5pt;"><strong>Date:</strong> _____ / _____ / 202___</div>
                <div style="font-size: 7.5pt; margin-top: 4px;"><strong>Place:</strong> ___________________</div>
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

    <div style="margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 65%; font-size: 7pt; color: #64748b; vertical-align: middle;">
                    Form Approved for Registration in Portal by: __________________________________
                </td>
                <td style="width: 35%; text-align: center; vertical-align: middle;">
                    <div style="border: 1px solid #cbd5e1; padding: 12px 6px; font-size: 7pt; color: #64748b; border-radius: 4px;">
                        Principal / Authorized Signatory & Official Stamp
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: right; font-size: 7pt; color: #94a3b8; margin-top: 10px;">
        Page 2 of 2 &nbsp;&bull;&nbsp; {{ $company->company_name ?? 'Sarvodaya Vidyalay' }} Employee Enrollment Form
    </div>

</body>
</html>
