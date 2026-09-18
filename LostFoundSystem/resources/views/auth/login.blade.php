@extends('layouts.app')
@section('title', 'Login — Lost and Found Tracking System')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-in">
        <div class="auth-header">
            <div class="icon-circle"><i class="fas fa-lock"></i></div>
            <h3>Welcome Back</h3>
            <p>Sign in to access your dashboard</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-key me-2"></i>Password</label>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-secondary" for="remember" style="font-size:0.88rem;">Remember me</label>
                </div>
                <a href="{{ url('/forgot-password') }}" class="text-link" style="font-size:0.88rem;">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary-gradient w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Sign In
            </button>
        </form>

        <div class="divider">or</div>

        <p class="text-center text-secondary" style="font-size:0.9rem;">
            Don't have an account? <a href="{{ url('/register') }}" class="text-link">Create one</a>
        </p>
    </div>
</div>
@endsection
