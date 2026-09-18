@extends('layouts.app')
@section('title', 'Forgot Password — Lost and Found Tracking System')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-in">
        <div class="auth-header">
            <div class="icon-circle"><i class="fas fa-unlock-alt"></i></div>
            <h3>Forgot Password?</h3>
            <p>Enter your email and we'll send you a reset link.</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/forgot-password') }}">
            @csrf
            <div class="form-floating mb-4">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>
            <button type="submit" class="btn btn-primary-gradient w-100 mb-3">
                <i class="fas fa-paper-plane me-2"></i> Send Reset Link
            </button>
        </form>

        <p class="text-center mt-3 text-secondary" style="font-size:0.9rem;">
            Remembered your password? <a href="{{ url('/login') }}" class="text-link">Back to Login</a>
        </p>
    </div>
</div>
@endsection
