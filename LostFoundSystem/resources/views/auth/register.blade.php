@extends('layouts.app')
@section('title', 'Register — Lost and Found Tracking System')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-in" style="max-width:520px;">
        <div class="auth-header">
            <div class="icon-circle"><i class="fas fa-user-plus"></i></div>
            <h3>Create Account</h3>
            <p>Join the campus lost and found community</p>
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

        <form method="POST" action="{{ url('/register') }}">
            @csrf
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="{{ old('name') }}" required autofocus>
                <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
            </div>
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="fas fa-key me-2"></i>Password</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm" required>
                        <label for="password_confirmation"><i class="fas fa-check-double me-2"></i>Confirm</label>
                    </div>
                </div>
            </div>
            <div class="form-floating mb-4">
                <select class="form-select" id="role" name="role" required>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
                <label for="role"><i class="fas fa-id-badge me-2"></i>I am a</label>
            </div>
            <button type="submit" class="btn btn-primary-gradient w-100 mb-3">
                <i class="fas fa-user-plus me-2"></i> Create Account
            </button>
        </form>

        <div class="divider">or</div>

        <p class="text-center text-secondary" style="font-size:0.9rem;">
            Already have an account? <a href="{{ url('/login') }}" class="text-link">Sign in</a>
        </p>
    </div>
</div>
@endsection
