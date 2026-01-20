<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Blood Finder - تسجيل الدخول')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          crossorigin="anonymous">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- ملفات التنسيق --}}
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
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

</head>

<body class="auth-body">

    {{-- الشريط العلوي --}}
    <header class="auth-topbar d-flex justify-content-between align-items-center px-4">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-link text-white p-0 back-btn">
                <i class="bi bi-arrow-right-circle fs-4"></i>
            </button>
        </div>
        <div class="text-white fw-semibold">
            @yield('topbar-title', 'تسجيل الدخول')
        </div>
    </header>

    {{-- المحتوى --}}
    <main class="auth-main d-flex justify-content-center align-items-start align-items-md-center">
        @yield('content')
    </main>

    {{-- سكربتات --}}
    <script src="{{ asset('assets/js/auth.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- SweetAlert رسائل --}}
    @if(session('success'))
    <script>
    Swal.fire({
        icon: 'success',
        title: 'نجاح',
        text: "{{ session('success') }}",
        confirmButtonText: 'تم'
    });
    </script>
    @endif

    @if(session('error'))
    <script>
    Swal.fire({
        icon: 'error',
        title: 'خطأ',
        text: "{{ session('error') }}",
        confirmButtonText: 'موافق'
    });
    </script>
    @endif

    @if(session('warning'))
    <script>
    Swal.fire({
        icon: 'warning',
        title: 'تنبيه',
        text: "{{ session('warning') }}",
        confirmButtonText: 'حسنا'
    });
    </script>
    @endif

    @if ($errors->any())
    <script>
    let errorMessages = `{!! implode("<br>", $errors->all()) !!}`;
    Swal.fire({
        icon: 'error',
        title: 'خطأ في الإدخال',
        html: errorMessages,
        confirmButtonText: 'موافق'
    });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
