<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hospital Panel')</title>

    {{-- Bootstrap RTL --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Google Font Tajawal --}}
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap"
    rel="stylesheet">

    {{-- Hospital Panel CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    @stack('styles')
</head>

<body class="bg-light">

    {{-- Overlay --}}
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    {{-- ========== Sidebar ========== --}}
    <aside id="sidebar" class="sidebar">
        <div class="sidebar-header">

            {{-- المستشفى --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="sidebar-logo"><i class="fas fa-hospital"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">
                            {{ auth()->user()->hospital->name ?? 'مستشفى' }}
                        </h5>
                        <small class="text-muted">نظام بنك الدم</small>
                    </div>
                </div>

                <button class="btn btn-sm sidebar-close" id="sidebarClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- معلومات المستخدم --}}
            <div class="admin-info mb-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="admin-avatar">
                        {{ mb_substr(auth()->user()->full_name, 0, 1) }}
                    </div>

                    <div>
                        <p class="mb-0 fw-semibold small">{{ auth()->user()->full_name }}</p>
                        <small class="text-primary">مدير المستشفى</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- ========== Sidebar Menu ========== --}}
        <nav class="sidebar-menu">

            <a href="{{ route('hospital.dashboard') }}"
               class="sidebar-item {{ request()->routeIs('hospital.dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge"></i> <span>لوحة التحكم</span>
            </a>

            <a href="{{ route('hospital.requests.index') }}"
               class="sidebar-item {{ request()->routeIs('hospital.requests.*') ? 'active' : '' }}">
                <i class="fas fa-inbox"></i> <span>طلبات الدم</span>
            </a>

            <a href="{{ route('hospital.inventory.index') }}"
               class="sidebar-item {{ request()->routeIs('hospital.inventory.*') ? 'active' : '' }}">
                <i class="fas fa-droplet"></i> <span>مخزون الدم</span>
            </a>

            <a href="{{ route('hospital.appointments.index') }}"
               class="sidebar-item {{ request()->routeIs('hospital.appointments.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> <span>مواعيد التبرع</span>
            </a>

            <a href="{{ route('hospital.notifications.index') }}"
               class="sidebar-item {{ request()->routeIs('hospital.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i> <span>الإشعارات</span>
            </a>

            <a href="{{ route('hospital.profile.index') }}"
               class="sidebar-item {{ request()->routeIs('hospital.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i> <span>الملف الشخصي</span>
            </a>

            <a href="{{ route('hospital.settings.index') }}"
               class="sidebar-item {{ request()->routeIs('hospital.settings.*') ? 'active' : '' }}">
                <i class="fas fa-gear"></i> <span>الإعدادات</span>
            </a>

        </nav>

        <div class="sidebar-footer">
            <button id="themeToggle" class="btn btn-outline-secondary w-100 mb-2">
                <i class="fas fa-moon"></i> الوضع الليلي
            </button>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger w-100" type="submit">
                    <i class="fas fa-right-from-bracket"></i> تسجيل الخروج
                </button>
            </form>
        </div>

    </aside>

    {{-- ========== Main Content ========== --}}
    <main id="mainContent" class="main-content">

        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">

                <div class="d-flex align-items-center gap-3">
                    <button id="sidebarToggle" class="btn btn-light"><i class="fas fa-bars"></i></button>

                    <div class="header-logo"><i class="fas fa-hospital"></i></div>

                    <div>
                        <h4 class="mb-0 fw-bold">@yield('title')</h4>
                        <small class="text-muted">مرحبا {{ auth()->user()->full_name }}</small>
                    </div>
                </div>

                {{-- User Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div class="avatar-sm">{{ mb_substr(auth()->user()->full_name, 0, 1) }}</div>
                        <span>{{ auth()->user()->full_name }}</span>
                        <i class="fas fa-chevron-down small"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('hospital.profile.index') }}" class="dropdown-item">
                            <i class="fas fa-user me-2"></i> الملف الشخصي
                        </a>

                        <a href="{{ route('hospital.profile.index') }}" class="dropdown-item">
                            <i class="fas fa-sliders me-2"></i> إعدادات الحساب
                        </a>

                        <div class="dropdown-divider"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger">
                                <i class="fas fa-right-from-bracket me-2"></i> تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </header>

        <div class="content-wrapper">
            @yield('content')
        </div>

    </main>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- رسائل النجاح --}}
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

{{-- رسائل الأخطاء --}}
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

@stack('scripts')
</body>
</html>
