@extends('layouts.app')
@section('title', 'Reset Password — Lost and Found Tracking System')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-in">
        <div class="auth-header">
            <div class="icon-circle"><i class="fas fa-lock"></i></div>
            <h3>Create a new password</h3>
            <p>Choose a strong password you do not use elsewhere.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update', $token) }}">
            @csrf
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="New password" minlength="8" required autofocus>
                <label for="password"><i class="fas fa-lock me-1"></i> New password</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" minlength="8" required>
                <label for="password_confirmation"><i class="fas fa-lock me-1"></i> Confirm new password</label>
            </div>
            <button type="submit" class="btn btn-primary-gradient w-100 py-3"><i class="fas fa-check me-2"></i> Reset password</button>
        </form>
    </div>
</div>
@endsection
