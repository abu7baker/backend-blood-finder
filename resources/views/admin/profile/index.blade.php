@extends('layouts.admin')

@section('title', 'الملف الشخصي')

@section('content')

<main id="mainContent" class="main-content">
    <div class="content-wrapper">

        <div class="profile-header shadow-sm p-4 rounded mb-4 bg-white">
            <div class="d-flex align-items-center flex-wrap gap-4">
                <div class="avatar-box">
                    <span>{{ mb_substr($user->full_name ?? 'A', 0, 1) }}</span>
                </div>

                <div class="flex-fill">
                    <h3 class="fw-bold mb-1">{{ $user->full_name }}</h3>

                    <p class="mb-1 text-muted">
                        <i class="fas fa-envelope text-danger ms-1"></i> {{ $user->email ?? '-' }}
                    </p>

                    <p class="mb-1 text-muted">
                        <i class="fas fa-phone text-danger ms-1"></i> {{ $user->phone ?? '-' }}
                    </p>

                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                        @if(($user->role?->name ?? '') === 'admin')
                            <span class="badge bg-dark px-3 py-2">
                                <i class="fas fa-user-shield ms-1"></i> مدير النظام
                            </span>
                        @elseif(($user->role?->name ?? '') === 'hospital')
                            <span class="badge bg-primary px-3 py-2">
                                <i class="fas fa-hospital ms-1"></i> مستشفى
                            </span>
                        @else
                            <span class="badge bg-secondary px-3 py-2">
                                <i class="fas fa-user ms-1"></i> مستخدم
                            </span>
                        @endif

                        @if($user->status === 'active')
                            <span class="status-badge status-active">نشط</span>
                        @elseif($user->status === 'pending')
                            <span class="status-badge status-pending">قيد المراجعة</span>
                        @else
                            <span class="status-badge status-blocked">محظور</span>
                        @endif
                    </div>
                </div>

                <div class="profile-quick">
                    <div class="quick-item">
                        <i class="fas fa-location-dot"></i>
                        <span>{{ $user->city ?? '-' }}</span>
                    </div>
                    <div class="quick-item">
                        <i class="fas fa-venus-mars"></i>
                        <span>{{ $user->gender ?? '-' }}</span>
                    </div>
                    <div class="quick-item">
                        <i class="fas fa-cake-candles"></i>
                        <span>{{ $user->age ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs custom-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#general">
                    <i class="fas fa-info-circle ms-1"></i> بيانات عامة
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#account">
                    <i class="fas fa-id-card ms-1"></i> بيانات الحساب
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#edit">
                    <i class="fas fa-edit ms-1"></i> تعديل الملف الشخصي
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#settings">
                    <i class="fas fa-user-cog ms-1"></i> إعدادات الحساب
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#password">
                    <i class="fas fa-lock ms-1"></i> كلمة المرور
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="general">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="title">الاسم الكامل</label>
                        <div class="info-box">{{ $user->full_name }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">الدور</label>
                        <div class="info-box">
                            {{ $user->role?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="title">المدينة</label>
                        <div class="info-box">{{ $user->city ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="title">الجنس</label>
                        <div class="info-box">{{ $user->gender ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="title">العمر</label>
                        <div class="info-box">{{ $user->age ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="account">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="title">البريد الالكتروني</label>
                        <div class="info-box">{{ $user->email ?? '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">رقم الهاتف</label>
                        <div class="info-box">{{ $user->phone ?? '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">هاتف الطوارئ</label>
                        <div class="info-box">{{ $user->emergency_phone ?? '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">الحالة</label>
                        <div class="info-box">{{ $user->status }}</div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="edit">
                <form action="{{ route('admin.profile.update') }}" method="POST" class="mt-3">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="title">الاسم الكامل</label>
                            <input type="text" class="form-control" name="full_name"
                                   value="{{ $user->full_name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="title">المدينة</label>
                            <select class="form-select" name="city">
                                <option value="">اختر المحافظة</option>
                                @include('partials.yemen-governorates-options', ['selected' => old('city', $user->city)])
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="title">العمر</label>
                            <input type="number" class="form-control" name="age" min="1"
                                   value="{{ $user->age }}">
                        </div>

                        <div class="col-md-6">
                            <label class="title">الجنس</label>
                            <select class="form-select" name="gender">
                                <option value="">-</option>
                                <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>ذكر</option>
                                <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>أنثى</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="title">هاتف الطوارئ</label>
                            <input type="text" class="form-control" name="emergency_phone"
                                   value="{{ $user->emergency_phone }}">
                        </div>
                    </div>

                    <button class="btn btn-success mt-3 px-4">
                        <i class="fas fa-save ms-1"></i> حفظ التعديلات
                    </button>
                </form>
            </div>

            <div class="tab-pane fade" id="settings">
                <form action="{{ route('admin.profile.credentials') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="title">البريد الالكتروني</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ $user->email }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="title">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ $user->phone }}" required>
                        </div>
                    </div>

                    <button class="btn btn-danger mt-3 px-4">
                        <i class="fas fa-save ms-1"></i> تحديث بيانات الحساب
                    </button>
                </form>
            </div>

            <div class="tab-pane fade" id="password">
                <div id="password-check-result"></div>

                <form action="{{ route('admin.profile.password') }}" method="POST" class="mt-3">
                    @csrf @method('PUT')

                    <div class="d-flex flex-column align-items-center gap-3">
                        <div class="position-relative password-field-wrapper">
                            <label class="title">كلمة المرور الحالية</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control password-field" required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>

                        <div class="position-relative password-field-wrapper">
                            <label class="title">كلمة المرور الجديدة</label>
                            <input type="password" id="new_password" name="password"
                                   class="form-control password-field" disabled required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>

                        <div class="position-relative password-field-wrapper">
                            <label class="title">تأكيد كلمة المرور</label>
                            <input type="password" id="confirm_password" name="password_confirmation"
                                   class="form-control password-field" disabled required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-primary mt-3 px-4">
                            <i class="fas fa-lock ms-1"></i> تحديث كلمة المرور
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</main>

<style>
.profile-header {
    border: 1px solid #f0f0f0;
    background: linear-gradient(135deg, #ffffff 0%, #fff6f6 100%);
}
.avatar-box {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8B0000, #FF6B6B);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 40px;
    color: #fff;
    box-shadow: 0 6px 14px rgba(139, 0, 0, 0.2);
}
.profile-quick {
    display: grid;
    gap: 10px;
    min-width: 180px;
}
.profile-quick .quick-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 1px solid #eee;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
    color: #6c757d;
}
.profile-quick .quick-item i {
    color: #dc3545;
}
.info-box {
    background: #fafafa;
    border: 1px solid #eee;
    padding: 12px 15px;
    border-radius: 10px;
    font-weight: 600;
}
.title {
    font-weight: bold;
}
.custom-tabs .nav-link {
    font-weight: 600;
    padding: 10px 20px;
}
.toggle-password {
    position: absolute;
    left: 10px;
    top: 38px;
    cursor: pointer;
    color: #888;
}
#password-check-result span {
    font-weight: bold;
    padding: 8px 12px;
}
.password-field-wrapper {
    width: 50%;
    min-width: 320px;
}
.password-field-wrapper .toggle-password {
    position: absolute;
    left: 10px;
    top: 43px;
    cursor: pointer;
    color: #888;
}
@media (max-width: 768px) {
    .profile-quick {
        width: 100%;
    }
    .password-field-wrapper {
        width: 100%;
        min-width: 0;
    }
}
</style>

<script>
document.querySelectorAll(".toggle-password").forEach(icon => {
    icon.addEventListener("click", function () {
        let input = this.previousElementSibling;
        input.type = input.type === "password" ? "text" : "password";
        this.classList.toggle("fa-eye-slash");
    });
});
</script>

<script>
document.getElementById("current_password").addEventListener("blur", function() {
    let pass = this.value;
    if (pass.length === 0) return;

    fetch("{{ route('admin.profile.checkPassword') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ current_password: pass })
    })
    .then(res => res.json())
    .then(data => {
        let box = document.getElementById("password-check-result");
        let newP = document.getElementById("new_password");
        let confirmP = document.getElementById("confirm_password");

        if (data.valid) {
            box.innerHTML = `<span class="text-success">OK: ${data.message}</span>`;
            newP.disabled = false;
            confirmP.disabled = false;
        } else {
            box.innerHTML = `<span class="text-danger">Error: ${data.message}</span>`;
            newP.disabled = true;
            confirmP.disabled = true;
        }
    });
});
</script>

@endsection
