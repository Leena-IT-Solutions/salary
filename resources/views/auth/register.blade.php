@extends('layouts.auth')

@section('content')

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="row">

            <div class="col-12 mb-4">
                <div class="form-floating">
                    <input type="text" class="form-control shadow-none @error('name') is-invalid @enderror" id="floating_name" name="name" value="{{ old('name') }}" placeholder="" autocomplete="name" autofocus>
                    <label for="floating_name">Name</label>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

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

            <div class="col-12 mb-4">
                <div class="form-floating">
                    <input type="text" class="form-control shadow-none @error('username') is-invalid @enderror" id="floating_username" name="username" value="{{ old('username') }}" placeholder="" autocomplete="username" autofocus>
                    <label for="floating_username">Username</label>
                    @error('username')
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
                    {{ __('Register') }}
                </button>
            </div>

        </div>
    </form>

@endsection