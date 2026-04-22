@extends('layouts.newapp')

@section('head')
<title>{{ $employee->first_name }} | Employee Portfolio</title>
<style>
    .profile-master-container {
        width: 100%;
        margin: 0;
    }
    
    .profile-hero-card {
        background: #ffffff;
        border-radius: 1.25rem;
        padding: 1.5rem; /* Reduced for mobile */
        border: 1px solid #edf2f7;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }

    @media (min-width: 768px) {
        .profile-hero-card {
            padding: 2rem;
            margin-bottom: 2rem;
        }
    }

    .avatar-frame {
        width: 100px; /* Slightly smaller for mobile */
        height: 100px;
        border-radius: 1rem;
        overflow: hidden;
        border: 3px solid #f8fafc;
        box-shadow: 0 0 0 1px #e2e8f0;
        margin: 0 auto; /* Center on mobile */
    }

    @media (min-width: 768px) {
        .avatar-frame {
            width: 110px;
            height: 110px;
            margin: 0;
        }
    }

    .avatar-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .summary-pill {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.7rem;
        padding: 0.4rem 0.8rem;
        border-radius: 100px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .overview-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1.25rem;
        border: 1px solid #edf2f7;
        height: 100%;
        transition: transform 0.2s;
    }

    .field-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        font-weight: 800;
        color: #94a3b8;
        letter-spacing: 1px;
        margin-bottom: 0.2rem;
    }

    .field-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .workspace-vault {
        background: white;
        border-radius: 1.25rem;
        border: 1px solid #edf2f7;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .workspace-nav-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
    }
</style>
@endsection

@section('content')

<div class="container-fluid py-3 py-md-4 profile-master-container">
    <!-- Top Identity Section -->
    <div class="profile-hero-card">
        <div class="row align-items-center g-3 g-md-4">
            <div class="col-12 col-md-auto">
                <div class="avatar-frame">
                    @if($employee->employee_photo)
                        <img src="{{ asset('storage' . $employee->employee_photo->media) }}" alt="Profile">
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center bg-light text-muted">
                            <i class="bi bi-person h1 mb-0"></i>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-md text-center text-md-start">
                <div class="d-flex flex-wrap flex-column flex-md-row align-items-center gap-2 gap-md-3 mb-2">
                    <h2 class="fw-900 text-slate-900 mb-0 fs-3">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</h2>
                    <div class="summary-pill mt-1 mt-md-0">
                        <i class="bi bi-person-vcard text-primary text-break"></i>
                        {{ $employee->employee_code }}
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2 gap-md-4 text-muted tiny fw-bold mt-2">
                    <span class="d-flex align-items-center"><i class="bi bi-briefcase me-1"></i> {{ $employee->employee_designation ? $employee->employee_designation->designation->designation : 'Employee' }}</span>
                    <span class="d-flex align-items-center"><i class="bi bi-building me-1"></i> {{ $employee->employee_department ? $employee->employee_department->department->department : 'General' }}</span>
                </div>
                <!-- Permanent Contact Indicators -->
                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3 mt-3">
                    <div class="summary-pill bg-white border">
                        <i class="bi bi-phone text-muted"></i>
                        <span class="fw-mono">{{ $employee->phone ?: 'No Number' }}</span>
                    </div>
                    <div class="summary-pill bg-white border">
                        <i class="bi bi-envelope-at text-muted"></i>
                        <span class="text-lowercase">{{ $employee->email ?: 'No Email' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-auto text-center mt-3 mt-xl-0">
                <div class="btn-group shadow-sm rounded-pill overflow-hidden w-100 w-md-auto">
                    <a href="tel:{{ $employee->phone }}" class="btn btn-primary px-4 py-2 fw-bold small"><i class="bi bi-telephone-fill me-2"></i>Call</a>
                    <a href="mailto:{{ $employee->email }}" class="btn btn-outline-primary px-4 py-2 fw-bold small">Email</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Profile Grid -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- Row 1: Bio-Data & Personal Profile (2x2 Grid Tier 1) -->
        <div class="col-12 col-md-6">
            <div class="overview-card h-100">
                <h6 class="fw-900 text-primary small text-uppercase mb-3 border-bottom pb-2"><i class="bi bi-person-badge me-2"></i>Bio-Data</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="field-label">Date of Birth</div>
                        <div class="field-value">{{ $employee->dob ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Gender</div>
                        <div class="field-value">{{ $employee->gender ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Blood Group</div>
                        <div class="field-value text-danger">{{ $employee->blood_group ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Religion</div>
                        <div class="field-value">{{ $employee->religion ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Caste</div>
                        <div class="field-value">{{ $employee->cast ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Sub-caste</div>
                        <div class="field-value">{{ $employee->subcast ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="overview-card h-100">
                <h6 class="fw-900 text-primary small text-uppercase mb-3 border-bottom pb-2"><i class="bi bi-globe me-2"></i>Personal Profile</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="field-label">Mobile Number</div>
                        <div class="field-value fw-mono">{{ $employee->phone ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Email Address</div>
                        <div class="field-value text-lowercase small">{{ $employee->email ?: '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="field-label">Marital Status</div>
                        <div class="field-value">{{ $employee->marital_status ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Mother Tongue</div>
                        <div class="field-value">{{ $employee->mothertongue ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Nationality</div>
                        <div class="field-value">{{ $employee->nationality ?: '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="field-label">Current Address</div>
                        <div class="field-value small lh-sm text-muted">
                            {{ $employee->employee_address ? $employee->employee_address->address . ', ' . $employee->employee_address->city . ' ' . $employee->employee_address->pincode : 'No registered address.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Academic & Compliance (2x2 Grid Tier 2) -->
        <div class="col-12 col-md-6">
            <div class="overview-card h-100">
                <h6 class="fw-900 text-primary small text-uppercase mb-3 border-bottom pb-2"><i class="bi bi-mortarboard me-2"></i>Academic</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="field-label">Qualification</div>
                        <div class="field-value">{{ $employee->qualification ?: '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="field-label">Highest Degree</div>
                        <div class="field-value text-muted">{{ $employee->degree ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="overview-card h-100 shadow-sm border-primary border-opacity-10">
                <h6 class="fw-900 text-primary small text-uppercase mb-3 border-bottom pb-2"><i class="bi bi-shield-lock me-2"></i>Compliance Vault</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="field-label">Aadhar Card</div>
                        <div class="field-value fw-mono">{{ $employee->aadhar ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">PAN Number</div>
                        <div class="field-value text-uppercase">{{ $employee->pan ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">PF Account</div>
                        <div class="field-value">{{ $employee->pf ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">UAN Number</div>
                        <div class="field-value">{{ $employee->uan ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">ESIC ID</div>
                        <div class="field-value">{{ $employee->esic ?: '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Biometric ID (Tag)</div>
                        <div class="field-value"><span class="badge bg-light text-dark border">{{ $employee->tagid ?: '—' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: Lifecycle & Role -->
        <div class="col-12">
            <div class="overview-card bg-light-subtle">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-3 border-md-end">
                        <h6 class="field-label mb-1">Date of Joining</h6>
                        <div class="field-value text-success fs-5">{{ $employee->doj ?: '—' }}</div>
                    </div>
                    <div class="col-12 col-md-3 border-md-end ps-md-4">
                        <h6 class="field-label mb-1">Employment Status</h6>
                        <div class="field-value">
                            @if($employee->doe)
                                <span class="text-danger small"><i class="bi bi-exclamation-triangle-fill me-1"></i>Exited ({{ $employee->doe }})</span>
                            @else
                                <span class="text-success small"><i class="bi bi-patch-check-fill me-1"></i>Active / In-Service</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6 ps-md-4">
                        <h6 class="field-label mb-1">Deployment Site</h6>
                        <div class="field-value small">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                            {{ $employee->employee_work_location ? $employee->employee_work_location->work_location->location_name : 'No active site' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrative Control Workspace -->
    <div class="workspace-vault">
        <div class="workspace-nav-header bg-light-subtle">
            <div class="row align-items-center text-center text-md-start g-2">
                <div class="col-12 col-md">
                    <h5 class="fw-900 mb-0 fs-6">Record Management Workspace</h5>
                    <p class="text-muted tiny mb-0 fw-semibold">Interactive panel for payroll, documentation, and compliance.</p>
                </div>
            </div>
        </div>
        <div class="p-0 overflow-hidden">
            <employee-update 
                :employee="{{ $employee }}" 
                :work_locations="{{ $work_locations }}" 
                :designations="{{ $designations }}" 
                :departments="{{ $departments }}"
                :leave_groups="{{ $leave_groups }}"
                :salary_groups="{{ $salary_groups }}"
                :services="{{ $services }}"></employee-update>
        </div>
    </div>

    <!-- Payout History & Document Dispatch -->
    <employee-payslip-history :employee_id="{{ $employee->id }}"></employee-payslip-history>
</div>

@endsection