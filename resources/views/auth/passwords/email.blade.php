@extends('layouts.auth')

@section('content')
<div class="container">

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="row">

            <div class="col-12 mb-4">
                <div class="form-floating">
                    <input type="email" class="form-control shadow-none @error('email') is-invalid @enderror" id="floating_email" name="email" value="{{ old('email') }}" placeholder="" autocomplete="email" autofocus>
                    <label for="floating_email">Email address</label>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    {{ __('Send Password Reset Link') }}
                </button>
            </div>

            <div class="col-12 mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <a class="text-decoration-none text-primary fw-semibold small d-inline-flex align-items-center" href="{{ route('login') }}">
                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                    </a>
                    <a class="text-decoration-none text-muted small d-inline-flex align-items-center" href="{{ url('/') }}">
                        <i class="bi bi-house me-1"></i> Home
                    </a>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
