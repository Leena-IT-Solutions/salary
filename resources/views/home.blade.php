@extends('layouts.newapp')

@section('head')
    <title>Dashboard Overview | SalaryManager</title>
@endsection

@section('content')

    <page-header title="Overview"></page-header>

    <div class="container-fluid px-4 py-4">

        <!-- Welcome Section -->
        <div class="row mb-5 mt-2" data-aos="fade-up">
            <div class="col-12">
                <h2 class="fw-bold text-dark">Welcome back, {{ Auth::user()->name }}!</h2>
                <p class="text-secondary">Here's what's happening in your organization today.</p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 p-4 border-0 shadow-premium">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-pill p-3">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-secondary fw-semibold text-uppercase small letter-spacing-1">Total Employees</h6>
                    <h1 class="fw-bold m-0">{{ $total_employee }}</h1>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 p-4 border-0 shadow-premium border-start border-success border-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-success bg-opacity-10 text-success rounded-pill p-3">
                            <i class="bi bi-person-check-fill fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-secondary fw-semibold text-uppercase small letter-spacing-1">Present Today</h6>
                    <div class="d-flex align-items-baseline gap-2">
                        <h1 class="fw-bold m-0 text-success">{{ $present }}</h1>
                        <span class="text-secondary small">/ {{ $total_employee }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 p-4 border-0 shadow-premium border-start border-danger border-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-pill p-3">
                            <i class="bi bi-person-x-fill fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-secondary fw-semibold text-uppercase small letter-spacing-1">Absent Today</h6>
                    <div class="d-flex align-items-baseline gap-2">
                        <h1 class="fw-bold m-0 text-danger">{{ $absent }}</h1>
                        <span class="text-secondary small">/ {{ $total_employee }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 p-4 border-0 shadow-premium border-start border-warning border-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-pill p-3">
                            <i class="bi bi-lightning-charge-fill fs-3"></i>
                        </div>
                        @if($pending_count > 0)
                            <span class="badge bg-danger rounded-pill pulse-badge">{{ $pending_count }}</span>
                        @endif
                    </div>
                    <h6 class="text-secondary fw-semibold text-uppercase small letter-spacing-1">Pending Approvals</h6>
                    <h1 class="fw-bold m-0 text-warning">{{ $pending_count }}</h1>
                </div>
            </div>
        </div>

        <!-- Secondary Row -->
        <div class="row g-4">
            <div class="col-lg-8" data-aos="fade-right" data-aos-delay="400">
                <div class="card p-4 border-0 shadow-premium h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0">Recent Pending Approvals</h5>
                        <a href="/approvals/pending" class="btn btn-sm btn-light text-primary fw-bold px-3">View All <i
                                class="bi bi-arrow-right ms-1"></i></a>
                    </div>

                    @if(count($recent_pending) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-0">
                                <thead>
                                    <tr class="text-secondary small text-uppercase">
                                        <th class="border-0 ps-0">Employee</th>
                                        <th class="border-0 text-center">Type</th>
                                        <th class="border-0">Date</th>
                                        <th class="border-0">Details</th>
                                        <th class="border-0 text-end pe-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_pending as $req)
                                        <tr>
                                            <td class="ps-0">
                                                <div class="fw-bold text-dark">{{ $req->employee->first_name }}
                                                    {{ $req->employee->last_name }}</div>
                                                <div class="small text-muted">{{ $req->employee->employee_code }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark fw-bold border">{{ $req->req_type }}</span>
                                            </td>
                                            <td class="small fw-semibold text-secondary">
                                                {{ \Carbon\Carbon::parse($req->on_date)->format('d M, Y') }}
                                            </td>
                                            <td class="small">{{ $req->display_details }}</td>
                                            <td class="text-end pe-0">
                                                <a href="/approvals/pending"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3">Approve</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div
                            class="d-flex flex-column align-items-center justify-content-center py-5 bg-light rounded-4 border border-dashed">
                            <i class="bi bi-check2-circle text-success fs-1 mb-2"></i>
                            <p class="text-secondary fw-bold m-0">All clear! No pending requests.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="500">
                <div class="d-flex flex-column gap-4">
                    <!-- Quick Actions -->
                    <div class="card p-4 border-0 shadow-premium">
                        <h5 class="fw-bold mb-4">Quick Actions</h5>
                        <div class="d-grid gap-3">
                            <a href="/run_payroll"
                                class="btn btn-primary text-start d-flex align-items-center justify-content-between shadow-sm p-3 rounded-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white bg-opacity-20 p-2 rounded-3"><i class="bi bi-cash-stack fs-5"></i>
                                    </div>
                                    <span>Run Monthly Payroll</span>
                                </div>
                                <i class="bi bi-chevron-right opacity-50"></i>
                            </a>
                            <a href="/attendance"
                                class="btn btn-light text-start d-flex align-items-center justify-content-between shadow-sm border p-3 rounded-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3"><i
                                            class="bi bi-fingerprint fs-5"></i></div>
                                    <span>Mark Attendance</span>
                                </div>
                                <i class="bi bi-chevron-right opacity-50"></i>
                            </a>
                            <a href="/employee/employee_manager"
                                class="btn btn-light text-start d-flex align-items-center justify-content-between shadow-sm border p-3 rounded-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3"><i
                                            class="bi bi-people fs-5"></i></div>
                                    <span>Employee Manager</span>
                                </div>
                                <i class="bi bi-chevron-right opacity-50"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Birthdays -->
                    <div class="card p-4 border-0 shadow-premium bg-gradient-primary-soft">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                <i class="bi bi-cake2-fill fs-5"></i>
                            </div>
                            <h5 class="fw-bold m-0">Birthdays This Month</h5>
                        </div>

                        @if(count($birthdays) > 0)
                            <div class="birthday-list d-flex flex-column gap-3">
                                @foreach($birthdays as $b)
                                    <div
                                        class="d-flex align-items-center justify-content-between p-2 rounded-3 hover-light transition-all">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle bg-primary bg-opacity-10 text-primary fw-bold text-uppercase d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; border-radius: 50%;">
                                                @if($b->employee_photo)
                                                    <img src="{{ asset('storage' . $b->employee_photo->media) }}"
                                                        class="rounded-circle w-100 h-100 object-fit-cover shadow-sm">
                                                @else
                                                    {{ substr($b->first_name, 0, 1) }}{{ substr($b->last_name, 0, 1) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ $b->first_name }} {{ $b->last_name }}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ $b->employee_code }}</div>
                                            </div>
                                        </div>
                                        <span
                                            class="badge bg-white text-primary border shadow-sm rounded-pill px-3">{{ $b->birth_date }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted small m-0">No birthdays this month. ✨</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection