@extends('layouts.auth')

@section('title', 'Create Account')
@section('heading', 'Create account')
@section('subheading', 'Join thousands of devotees in Metro Cebu')

@section('content')
    <div class="auth-tabs">
        <a href="{{ route('login') }}"    class="auth-tab">Sign In</a>
        <a href="{{ route('register') }}" class="auth-tab active">Sign Up</a>
    </div>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="field">
            <label class="form-label" for="name">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="giya-input @error('name') is-invalid @enderror"
                   placeholder="Juan dela Cruz" required autofocus autocomplete="name">
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label class="form-label" for="email">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="giya-input @error('email') is-invalid @enderror"
                   placeholder="juan@email.com" required autocomplete="email">
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
                <input id="password" type="password" name="password"
                       class="giya-input @error('password') is-invalid @enderror"
                       placeholder="Minimum 8 characters" required autocomplete="new-password">
                <button type="button" class="input-suffix" onclick="giyaTogglePassword('password', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('password')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="input-wrap">
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="giya-input" placeholder="Re-enter your password" required autocomplete="new-password">
                <button type="button" class="input-suffix" onclick="giyaTogglePassword('password_confirmation', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-w-full">Create Account</button>
    </form>

    <p class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </p>
@endsection
