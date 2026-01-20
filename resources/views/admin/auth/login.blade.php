@extends('layouts.auth')

@section('title', 'تسجيل الدخول - لوحة التحكم')
@section('topbar-title', 'تسجيل الدخول')



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


