@extends('layouts.auth')

@section('title', 'تسجيل الدخول - لوحة التحكم')
@section('topbar-title', 'تسجيل الدخول')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-950: #2b0a0a;
            --brand-900: #6b0f0f;
            --brand-700: #b91c1c;
            --brand-500: #ef4444;
            --accent-500: #f97316;
            --surface: #fff7f7;
            --ink: #0f172a;
            --muted: #94a3b8;
            --line: #f1d7d7;
        }

        .auth-body {
            font-family: "Cairo", "Tajawal", sans-serif;
            background:
                radial-gradient(circle at 15% 20%, rgba(239, 68, 68, 0.14), transparent 45%),
                radial-gradient(circle at 85% 10%, rgba(249, 115, 22, 0.16), transparent 40%),
                linear-gradient(120deg, #fff2f2 0%, #fcefee 100%);
            color: var(--ink);
        }

        .auth-topbar {
            display: none;
        }

        .auth-main {
            padding: 32px 18px 48px;
            align-items: center;
        }

        .login-shell {
            width: min(1100px, 100%);
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: #fff;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(107, 15, 15, 0.2);
            border: 1px solid rgba(107, 15, 15, 0.08);
            min-height: 560px;
        }

        .login-form {
            padding: 48px 44px;
            background: #fff;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: var(--surface);
            border: 1px solid var(--line);
            padding: 8px;
            object-fit: contain;
        }

        .brand-title {
            font-weight: 900;
            font-size: 22px;
            margin: 0;
        }

        .brand-sub {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .form-heading h2 {
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--brand-900);
        }

        .form-heading p {
            color: #64748b;
            margin-bottom: 26px;
        }

        .field-wrap {
            position: relative;
        }

        .field-wrap .form-control {
            border-radius: 16px;
            border: 1px solid var(--line);
            background: var(--surface);
            padding: 14px 16px 14px 44px;
            font-size: 15px;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field-wrap .form-control:focus {
            border-color: var(--brand-500);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18);
        }

        .field-wrap .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 18px;
        }

        .login-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 16px 0 24px;
            gap: 12px;
            font-size: 14px;
        }

        .btn-login {
            border-radius: 16px;
            padding: 12px 18px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--brand-700), var(--brand-500));
            border: none;
            color: #fff;
            box-shadow: 0 12px 24px rgba(185, 28, 28, 0.25);
        }

        .btn-login:hover {
            opacity: 0.95;
        }

        .helper-links {
            display: grid;
            gap: 12px;
            margin-top: 22px;
            font-size: 14px;
        }

        .helper-links a {
            text-decoration: none;
        }

        .login-aside {
            background: linear-gradient(150deg, #5b0f0f 0%, #b91c1c 100%);
            color: #fff;
            position: relative;
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .login-aside::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.16), transparent 65%);
            top: -80px;
            left: -80px;
        }

        .login-aside::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1), transparent 60%);
            bottom: -60px;
            right: -40px;
        }

        .aside-content {
            position: relative;
            z-index: 1;
        }

        .aside-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.35);
            margin-bottom: 22px;
        }

        .aside-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
        }

        .aside-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .aside-sub {
            opacity: 0.85;
            margin-bottom: 28px;
        }

        .aside-list {
            display: grid;
            gap: 14px;
        }

        .aside-item {
            display: flex;
            gap: 12px;
            align-items: center;
            background: rgba(255, 255, 255, 0.12);
            padding: 12px 14px;
            border-radius: 14px;
        }

        .aside-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.2);
            font-size: 18px;
        }

        .aside-footer {
            font-size: 13px;
            opacity: 0.8;
            margin-top: 32px;
        }

        @media (max-width: 992px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-aside {
                order: -1;
            }
        }

        @media (max-width: 576px) {
            .login-form,
            .login-aside {
                padding: 32px 24px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-shell">
        <section class="login-form">
            <div class="brand-row">

                <div>
                    <h1 class="brand-title">Blood Finder</h1>
                    <p class="brand-sub">لوحة تحكم الإدارة</p>
                </div>
            </div>

            <div class="form-heading">
                <h2>تسجيل الدخول</h2>
                <p>أدخل بياناتك للوصول إلى لوحة التحكم الإدارية.</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="mb-3 field-wrap">
                    <input type="text" name="email_or_phone" class="form-control" placeholder="البريد الإلكتروني أو رقم الهاتف">
                    <span class="input-icon"><i class="bi bi-person"></i></span>
                </div>

                <div class="mb-2 field-wrap">
                    <input type="password" name="password" class="form-control" id="adminPassword" placeholder="كلمة المرور">
                    <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                </div>

                <div class="login-actions">
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="form-check-input m-0">
                        تذكرني
                    </label>
                    <a href="#" class="text-decoration-none text-danger">نسيت كلمة المرور؟</a>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login">تسجيل الدخول</button>
                </div>

                <div class="helper-links">
                    <a href="#" class="btn btn-outline-secondary rounded-4">إنشاء حساب جديد</a>
                    <a href="{{ route('hospital.register.step1') }}" class="btn btn-outline-primary rounded-4">
                        <i class="bi bi-hospital ms-1"></i>
                        إنشاء حساب مستشفى
                    </a>
                </div>
            </form>
        </section>

        <aside class="login-aside">
            <div class="aside-content">
                <div class="aside-logo-wrap">
                    <img src="{{ asset('assets/images/icon.jpeg') }}" alt="Blood Finder" class="aside-logo">
                </div>
                <h3 class="aside-title">منصة موثوقة لإدارة الدم</h3>
                <p class="aside-sub">راقب الطلبات، نسق المواعيد، وتحكم في المخزون من لوحة واحدة.</p>

                <div class="aside-list">
                    <div class="aside-item">
                        <div class="aside-icon"><i class="bi bi-shield-check"></i></div>
                        <div>حماية بيانات متقدمة وتنبيهات فورية</div>
                    </div>
                    <div class="aside-item">
                        <div class="aside-icon"><i class="bi bi-graph-up"></i></div>
                        <div>متابعة دقيقة للطلبات والمخزون لحظة بلحظة</div>
                    </div>
                    <div class="aside-item">
                        <div class="aside-icon"><i class="bi bi-clock-history"></i></div>
                        <div>إدارة سريعة للمواعيد والاستجابات العاجلة</div>
                    </div>
                </div>
            </div>

            <div class="aside-footer">
                فريق الدعم متاح دائمًا لمساعدتك • support@bloodfinder.app
            </div>
        </aside>
    </div>
@endsection


