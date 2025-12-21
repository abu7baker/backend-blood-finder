<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Blood Finder')</title>

    {{-- Bootstrap RTL --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Google Font Tajawal --}}
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap"
          rel="stylesheet">

    {{-- CSS الخاص بلوحة التحكم --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    @stack('styles')
</head>
<body class="bg-light">

    {{-- Overlay للسايد بار في الموبايل --}}
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    {{-- ===== Sidebar ===== --}}
    <aside id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="sidebar-logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">لوحة الإدارة</h5>
                        <small class="text-muted">Blood Finder</small>
                    </div>
                </div>
                <button class="btn btn-sm sidebar-close" id="sidebarClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- معلومات المشرف --}}
            <div class="admin-info">
                <div class="d-flex align-items-center gap-3">
                    <div class="admin-avatar">
                        {{ mb_substr(auth()->user()->full_name ?? 'أبوبكر', 0, 1) }}
                    </div>
                    <div class="flex-fill">
                        <p class="mb-0 fw-medium small">
                            {{ auth()->user()->full_name ?? 'أبوبكر محمد طاهر' }}
                        </p>
                        <small class="text-danger">مدير النظام</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- عناصر السايد بار --}}
        <nav class="sidebar-menu">
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge"></i>
                <span>لوحة التحكم</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>إدارة المستخدمين</span>
            </a>

            <a href="{{ route('admin.hospitals.index') }}"
               class="sidebar-item {{ request()->routeIs('admin.hospitals.*') ? 'active' : '' }}">
                <i class="fas fa-hospital"></i>
                <span>إدارة المستشفيات</span>
            </a>

            <a href="{{ route('admin.inventory.index') }}" class="sidebar-item">
                <i class="fas fa-boxes-stacked"></i>
                <span>إدارة المخزون</span>
            </a>

            <a href="{{ route('admin.requests.index') }}" class="sidebar-item">
                <i class="fas fa-droplet"></i>
                <span>إدارة الطلبات</span>
            </a>
            <a href="{{ route('admin.donations.index') }}" class="sidebar-item">
                <i class="fas fa-droplet"></i>
                <span>إدارة التبرعات</span>
            </a>

            <a href="#" class="sidebar-item">
                <i class="fas fa-chart-bar"></i>
                <span>التقارير والإحصائيات</span>
            </a>

            <a href="#" class="sidebar-item">
                <i class="fas fa-shield-halved"></i>
                <span>الأمان والصلاحيات</span>
            </a>

            <a href="#" class="sidebar-item">
                <i class="fas fa-gear"></i>
                <span>إعدادات النظام</span>
            </a>
        </nav>

        {{-- زر الوضع الليلي + تسجيل الخروج --}}
        <div class="sidebar-footer">
            <button id="themeToggle" class="btn btn-outline-secondary w-100 mb-2" type="button" data-theme-toggle>
                <i class="fas fa-moon"></i>
                <span>الوضع الليلي</span>
            </button>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger w-100" type="submit">
                    <i class="fas fa-right-from-bracket"></i>
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== Main Content ===== --}}
    <main id="mainContent" class="main-content">
        {{-- الهيدر العلوي --}}
        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="header-logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold">@yield('title')</h4>
                        <p class="mb-0 text-muted small">
                            مرحباً {{ auth()->user()->full_name ?? '' }} - مدير النظام
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary" id="themeToggleHeader" type="button" data-theme-toggle>
                        <i class="fas fa-moon"></i>
                    </button>

                    {{-- قائمة المستخدم --}}
                    <div class="dropdown">
                        <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                            <div class="avatar-sm">
                                {{ mb_substr(auth()->user()->full_name ?? 'أ', 0, 1) }}
                            </div>
                            <span class="small">{{ auth()->user()->full_name ?? '' }}</span>
                            <i class="fas fa-chevron-down small"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user me-2"></i> الملف الشخصي
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-sliders me-2"></i> إعدادات الحساب
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="fas fa-right-from-bracket me-2"></i> تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- محتوى الصفحة --}}
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    {{-- ===== JS ===== --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

{{-- إضافة SweetAlert2 --}}
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
