@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Reset password')
@section('subheading', "We'll send a reset link to your email")

@section('content')
    @error('email')
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <p style="font-size:13px;color:var(--text-muted);line-height:1.7;margin-bottom:20px">
        Enter the email address linked to your GIYA account and we will send you
        a link to set a new password. The link is valid for 60 minutes.
    </p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="field">
            <label class="form-label" for="email">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="giya-input @error('email') is-invalid @enderror"
                   placeholder="juan@email.com" required autofocus autocomplete="email">
        </div>

        <button type="submit" class="btn btn-primary btn-w-full">Send Reset Link</button>
    </form>

    <p class="auth-footer">
        <a href="{{ route('login') }}" style="color:var(--text-muted);font-weight:400">← Back to sign in</a>
    </p>
@endsection
