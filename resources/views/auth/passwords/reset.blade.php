@extends('layouts.auth')

@section('content')

    <form method="POST" action="{{ route('password.update') }}">
        
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="row">

            <div class="col-12 mb-4">
                <div class="form-floating">
                    <input type="email" class="form-control shadow-none @error('email') is-invalid @enderror" id="floating_email" name="email" value="{{ $email ?? old('email') }}" placeholder="" autocomplete="email" autofocus>
                    <label for="floating_email">Email address</label>
                    @error('email')
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
                <div class="form-floating">
                    <input type="password" class="form-control shadow-none @error('password_confirmation') is-invalid @enderror" id="floating_password_confirmation" name="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="" autocomplete="password_confirmation" autofocus>
                    <label for="floating_password_confirmation">Confirm Password</label>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    {{ __('Reset Password') }}
                </button>
            </div>

        </div>
    </form>

@endsection
