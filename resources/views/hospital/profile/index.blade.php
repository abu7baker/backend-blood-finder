@extends('hospital.layouts.hospital')

@section('title', 'الملف الشخصي')

@section('content')

<main id="mainContent" class="main-content">
    <div class="content-wrapper">


        {{-- بطاقة الملف الشخصي --}}
        <div class="profile-header shadow-sm p-4 rounded mb-4 bg-white">
            <div class="d-flex align-items-center gap-4">

                <div class="avatar-box">
                    <i class="fas fa-hospital-user"></i>
                </div>

                <div>
                    <h3 class="fw-bold mb-1">{{ $hospital->name }}</h3>

                    <p class="mb-1 text-muted">
                        <i class="fas fa-envelope text-danger ms-1"></i> {{ $user->email }}
                    </p>

                    <p class="mb-1 text-muted">
                        <i class="fas fa-phone text-danger ms-1"></i> {{ $user->phone ?? "—" }}
                    </p>

                    @if($hospital->status === 'verified')
                        <span class="badge bg-success p-2 px-3"><i class="fas fa-check-circle ms-1"></i> نشط</span>
                    @elseif($hospital->status === 'pending')
                        <span class="badge bg-warning text-dark p-2 px-3"><i class="fas fa-clock ms-1"></i> قيد المراجعة</span>
                    @else
                        <span class="badge bg-danger p-2 px-3"><i class="fas fa-ban ms-1"></i> محظور</span>
                    @endif

                </div>
            </div>
        </div>

        {{-- التابات --}}
        <ul class="nav nav-tabs custom-tabs mb-4">

            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#general">
                    <i class="fas fa-info-circle ms-1"></i> المعلومات العامة
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#account">
                    <i class="fas fa-id-card ms-1"></i> بيانات الحساب
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#edit">
                    <i class="fas fa-edit ms-1"></i> تعديل بيانات المستشفى
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#settings">
                    <i class="fas fa-user-cog ms-1"></i> إعدادات الحساب
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#password">
                    <i class="fas fa-lock ms-1"></i> تغيير كلمة المرور
                </a>
            </li>

        </ul>

        {{-- محتوى التابات --}}
        <div class="tab-content">

            {{-- المعلومات العامة --}}
            <div class="tab-pane fade show active" id="general">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="title">اسم المستشفى</label>
                        <div class="info-box">{{ $hospital->name }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">المدينة</label>
                        <div class="info-box">{{ $hospital->city }}</div>
                    </div>

                    <div class="col-md-12">
                        <label class="title">الموقع</label>
                        <div class="info-box">{{ $hospital->location }}</div>
                    </div>

                </div>
            </div>

            {{-- بيانات الحساب --}}
            <div class="tab-pane fade" id="account">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="title">الإدارة</label>
                        <div class="info-box">{{ $user->full_name }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">البريد الإلكتروني</label>
                        <div class="info-box">{{ $user->email }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">رقم الهاتف</label>
                        <div class="info-box">{{ $user->phone }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="title">رقم الطوارئ</label>
                        <div class="info-box">{{ $user->emergency_phone ?? "—" }}</div>
                    </div>

                </div>
            </div>

            {{-- تعديل بيانات المستشفى --}}
            <div class="tab-pane fade" id="edit">

                <form action="{{ route('hospital.profile.update') }}" method="POST" class="mt-3">
                    @csrf @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="title">اسم المستشفى</label>
                            <input type="text" class="form-control" name="name" value="{{ $hospital->name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="title">المدينة</label>
                            <input type="text" class="form-control" name="city" value="{{ $hospital->city }}" required>
                        </div>

                        <div class="col-md-12">
                            <label class="title">الموقع</label>
                            <input type="text" class="form-control" name="location" value="{{ $hospital->location }}" required>
                        </div>

                        <div class="col-md-12">
                            <label class="title">رقم الطوارئ</label>
                            <input type="text" class="form-control" name="emergency_phone" value="{{ $user->emergency_phone }}">
                        </div>

                    </div>

                    <button class="btn btn-success mt-3 px-4">
                        <i class="fas fa-save ms-1"></i> حفظ التعديلات
                    </button>

                </form>

            </div>

            {{-- إعدادات الحساب --}}
            <div class="tab-pane fade" id="settings">

                <form action="{{ route('hospital.profile.credentials') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="title">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="title">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" required>
                        </div>

                    </div>

                    <button class="btn btn-danger mt-3 px-4">
                        <i class="fas fa-save ms-1"></i> حفظ التغييرات
                    </button>

                </form>

            </div>

            {{-- تغيير كلمة المرور --}}
           <div class="tab-pane fade" id="password">

    <div id="password-check-result"></div>

    <form action="{{ route('hospital.profile.password') }}" method="POST" class="mt-3">
        @csrf @method('PUT')

        <div class="d-flex flex-column align-items-center gap-3">

            <!-- كلمة المرور الحالية -->
            <div class="position-relative password-field-wrapper">
                <label class="title">كلمة المرور الحالية</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control password-field" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>

            <!-- كلمة المرور الجديدة -->
            <div class="position-relative password-field-wrapper">
                <label class="title">كلمة المرور الجديدة</label>
                <input type="password" id="new_password" name="password"
                       class="form-control password-field" disabled required>
                <i class="fas fa-eye toggle-password"></i>
            </div>

            <!-- تأكيد كلمة المرور -->
            <div class="position-relative password-field-wrapper">
                <label class="title">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="password_confirmation"
                       class="form-control password-field" disabled required>
                <i class="fas fa-eye toggle-password"></i>
            </div>

        </div>

        <div class="text-center">
            <button class="btn btn-primary mt-3 px-4">
                <i class="fas fa-lock ms-1"></i> تغيير كلمة المرور
            </button>
        </div>

    </form>

</div>

        </div>

    </div>
</main>

{{-- ================= CSS ================= --}}
<style>

.avatar-box {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 45px;
    color: #dc3545;
    box-shadow: 0 3px 10px #ddd;
}
.info-box {
    background: #fafafa;
    border: 1px solid #eee;
    padding: 12px 15px;
    border-radius: 10px;
    font-weight: 600;
}
.title { font-weight: bold; }
.custom-tabs .nav-link { font-weight: 600; padding: 10px 20px; }
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
    width: 50%;              /* نصف عرض الصفحة */
    min-width: 320px;        /* يمنع أن يصبح صغير جداً */
}

.password-field-wrapper .toggle-password {
    position: absolute;
    left: 10px;
    top: 43px;
    cursor: pointer;
    color: #888;
}

</style>

{{-- ================= JS: إظهار كلمة السر ================= --}}
<script>
document.querySelectorAll(".toggle-password").forEach(icon => {
    icon.addEventListener("click", function () {
        let input = this.previousElementSibling;
        input.type = input.type === "password" ? "text" : "password";
        this.classList.toggle("fa-eye-slash");
    });
});
</script>

{{-- ================= JS: التحقق الفوري من كلمة المرور ================= --}}
<script>
document.getElementById("current_password").addEventListener("blur", function() {

    let pass = this.value;
    if (pass.length === 0) return;

    fetch("{{ route('hospital.profile.checkPassword') }}", {
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
            box.innerHTML = `<span class="text-success">✔ ${data.message}</span>`;
            newP.disabled = false;
            confirmP.disabled = false;
        } else {
            box.innerHTML = `<span class="text-danger">❌ ${data.message}</span>`;
            newP.disabled = true;
            confirmP.disabled = true;
        }
    });
});
</script>

@endsection
