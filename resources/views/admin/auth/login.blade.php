@extends('layouts.auth')

@section('title', 'تسجيل الدخول - لوحة التحكم')
@section('topbar-title', 'تسجيل الدخول')

@section('content')
<div class="auth-card shadow-lg rounded-4 bg-white">

    {{-- اللوجو --}}
    <div class="text-center mb-4">
        <div class="blood-logo mx-auto mb-3">
            <i class="bi bi-droplet-fill"></i>
        </div>
        <h2 class="fw-bold welcome-title">
            مرحبًا بك في <span class="brand-text">Blood Finder</span>
        </h2>
        <p class="text-muted small mb-0">تسجيل دخول الادمن</p>
    </div>

    {{-- نموذج تسجيل الدخول --}}
    <form action="{{ route('login.post') }}" method="POST" class="text-end">
        @csrf

        {{-- البريد أو الهاتف --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">البريد الإلكتروني أو رقم الهاتف</label>
            <div class="input-group input-group-lg auth-input-group">
                <input type="text" name="email_or_phone" class="form-control" placeholder="example@gmail.com">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            </div>
        </div>

        {{-- كلمة المرور --}}
        <div class="mb-1">
            <label class="form-label fw-semibold">كلمة المرور</label>
            <div class="input-group input-group-lg auth-input-group">
                <input type="password" name="password" class="form-control" placeholder="••••••">
                <span class="input-group-text password-toggle">
                    <i class="bi bi-eye-slash" id="passwordToggleIcon"></i>
                </span>
            </div>
        </div>

        <div class="mb-3 text-start">
            <a href="#" class="link-danger small text-decoration-none">نسيت كلمة المرور؟</a>
        </div>

        {{-- زر تسجيل الدخول --}}
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-login">تسجيل الدخول</button>
        </div>

        <div class="text-center text-muted mb-3 small">
            أو سجل الدخول باستخدام
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="button" class="btn btn-outline-secondary w-50">
                <i class="bi bi-facebook me-1"></i> Facebook
            </button>
            <button type="button" class="btn btn-outline-secondary w-50">
                <i class="bi bi-google me-1"></i> Google
            </button>
        </div>

        {{-- إنشاء حساب مستخدم --}}
        <div class="text-center mb-2 text-muted small">
            ليس لديك حساب؟
        </div>

        <div class="d-grid">
            <a href="#" class="btn btn-danger btn-create-account">
                إنشاء حساب جديد
            </a>
        </div>

        {{-- إنشاء حساب مستشفى --}}
        <div class="text-center mt-3 text-muted small">
            هل تمثل مستشفى؟
        </div>

        <div class="d-grid mt-2">
    <a href="{{ route('hospital.register.step1') }}"
       class="btn btn-outline-primary btn-create-account">
        <i class="bi bi-hospital me-2"></i>
        إنشاء حساب مستشفى
    </a>
</div>


    </form>
</div>
@endsection
