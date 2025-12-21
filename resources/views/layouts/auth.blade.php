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

    @stack('styles')
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
