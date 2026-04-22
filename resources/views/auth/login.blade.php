@extends('layouts.auth')

@section('content')

@if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="row">

            <div class="col-12 mb-4">
                <div class="form-floating">
                    <input type="text" class="form-control shadow-none @error('user') is-invalid @enderror" id="floating_user" name="user" value="{{ old('user') }}" placeholder="" autocomplete="user" autofocus>
                    <label for="floating_user">Username/Email</label>
                    @error('user')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="form-floating">
                    <input type="password" class="form-control shadow-none @error('password') is-invalid @enderror" id="floating_password" name="password" value="{{ old('password') }}" placeholder="" autocomplete="password" autofocus>
                    <label for="floating_password">Password</label>
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
            </div>

            <div class="col-12 mb-4">
                <div class="form-check">
                    <input class="form-check-input shadow-none" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    {{ __('Login') }}
                </button>
                @if (Route::has('password.request'))
                    <a class="btn btn-link" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>

            <div class="col-12 mt-4 pt-3 border-top text-center">
                <p class="text-muted mb-0 small">Are you a new employee?</p>
                <a class="fw-bold text-decoration-none" href="{{ route('register') }}">
                    {{ __('Create Employee Account') }}
                </a>
            </div>

        </div>
    </form>

@endsection
