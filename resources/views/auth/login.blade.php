@extends('layouts.auth')

@section('title', 'Sign In')
@section('heading', 'Welcome back')
@section('subheading', 'Sign in to continue your pilgrimage journey')

@section('content')
    <div class="auth-tabs">
        <a href="{{ route('login') }}"    class="auth-tab active">Sign In</a>
        <a href="{{ route('register') }}" class="auth-tab">Sign Up</a>
    </div>

    @error('email')
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <div class="field">
            <label class="form-label" for="email">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="giya-input @error('email') is-invalid @enderror"
                   placeholder="juan@email.com" required autofocus autocomplete="email">
        </div>

        <div class="field">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
                <input id="password" type="password" name="password"
                       class="giya-input @error('password') is-invalid @enderror"
                       placeholder="••••••••" required autocomplete="current-password">
                <button type="button" class="input-suffix"
                        onclick="giyaTogglePassword('password', this)" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('password')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <label class="d-flex align-items-center gap-2 m-0" style="font-size:13px;color:var(--text-muted);cursor:pointer">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            <a href="{{ route('password.request') }}" style="font-size:13px;color:var(--primary);font-weight:600">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary btn-w-full">Sign In</button>
    </form>

    <p class="auth-footer">
        No account yet? <a href="{{ route('register') }}">Create one</a>
    </p>
@endsection
