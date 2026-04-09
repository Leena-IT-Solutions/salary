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
        <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100 p-4 border-0 shadow-premium">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-pill p-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Live</span>
                </div>
                <h6 class="text-secondary fw-semibold text-uppercase small letter-spacing-1">Total Employees</h6>
                <div class="d-flex align-items-baseline gap-2">
                    <h1 class="fw-bold m-0">{{ $total_employee }}</h1>
                    <span class="text-success small"><i class="bi bi-arrow-up"></i> 2%</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100 p-4 border-0 shadow-premium">
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
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ ($total_employee > 0) ? ($present / $total_employee * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card h-100 p-4 border-0 shadow-premium">
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
    </div>

    <!-- Secondary Row -->
    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-right" data-aos-delay="400">
            <div class="card p-4 border-0 shadow-premium min-vh-25">
                <h5 class="fw-bold mb-4">Payroll Statistics</h5>
                <div class="d-flex align-items-center justify-content-center h-100 py-5 bg-light rounded-2xl">
                    <p class="text-secondary m-0">Payroll data chart will appear here after calculation.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-left" data-aos-delay="500">
            <div class="card p-4 border-0 shadow-premium h-100">
                <h5 class="fw-bold mb-4">Quick Actions</h5>
                <div class="d-grid gap-3">
                    <a href="/run_payroll" class="btn btn-primary text-start d-flex align-items-center justify-content-between shadow-sm">
                        <span>Run Monthly Payroll</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="/attendance" class="btn btn-light text-start d-flex align-items-center justify-content-between shadow-sm border">
                        <span>Mark Attendance</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="/employee/employee_manager" class="btn btn-light text-start d-flex align-items-center justify-content-between shadow-sm border">
                        <span>Employee Manager</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection