@extends('layouts.newapp')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="text-center animate__animated animate__fadeIn">
        <div class="mb-4">
            <div class="d-inline-flex bg-danger bg-opacity-10 p-4 rounded-circle mb-4">
                <i class="bi bi-shield-lock text-danger fs-1"></i>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-3">Access Restricted</h1>
            <p class="text-secondary fs-5 mx-auto" style="max-width: 500px;">
                You do not have the necessary permissions to access this area. 
                If you believe this is an error, please contact your System Administrator.
            </p>
        </div>
        
        <div class="d-flex justify-content-center gap-3 mt-5">
            <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2 rounded-pill d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Go Back
            </a>
            <a href="/" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-house-door"></i>
                Dashboard
            </a>
        </div>
    </div>
</div>

<style>
    .rounded-pill { border-radius: 50rem !important; }
    .transition-all { transition: all 0.3s ease; }
    .btn-primary { background: linear-gradient(135deg, #4f46e5, #6366f1); border: none; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
</style>
@endsection
