@extends('layouts.auth')

@section('title', 'إنشاء حساب مستشفى - الخطوة الأولى')

@section('content')

<div class="auth-card shadow-lg rounded-4 bg-white">

    {{-- العنوان العلوي --}}
    <div class="wizard-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <span class="small fw-bold">1 / 2</span>
            <h4 class="fw-bold">إنشاء حساب جديد</h4>
        </div>

        <div class="progress mt-3" style="height: 4px;">
            <div class="progress-bar bg-danger" style="width: 50%;"></div>
        </div>
    </div>

    {{-- نموذج --}}
    <form action="{{ route('hospital.register.step1.post') }}" method="POST" class="wizard-body" dir="rtl">
        @csrf

        <h5 class="fw-bold text-center mb-4">بيانات المستشفى</h5>

        <div class="row g-3">

            {{-- اسم المستشفى --}}
            <div class="col-12">
                <label class="form-label fw-semibold">اسم المستشفى *</label>
                <input type="text" name="name" class="form-control" placeholder="مثال: مستشفى الثورة العام" required>
            </div>

            {{-- البريد --}}
            <div class="col-12">
                <label class="form-label fw-semibold">البريد الإلكتروني الرسمي *</label>
                <div class="input-group">
                    <input type="email" name="email" class="form-control" placeholder="hospital@example.com" required>
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                </div>
            </div>

            {{-- الهاتف --}}
            <div class="col-12">
                <label class="form-label fw-semibold">رقم الهاتف *</label>
                <div class="input-group">
                    <input type="text" name="phone" class="form-control" placeholder="+967 123 456" required>
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                </div>
            </div>

            {{-- كلمة المرور --}}
            <div class="col-12">
                <label class="form-label fw-semibold">كلمة المرور *</label>
                <div class="input-group">
                    <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                </div>
            </div>

            {{-- المدينة --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">المدينة *</label>
                <input type="text" name="city" class="form-control" placeholder="صنعاء" required>
            </div>

            {{-- الموقع --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">الموقع *</label>
                <input type="text" name="location" class="form-control" placeholder="التحرير - جولة المصباحي" required>
            </div>

        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-danger px-5">التالي</button>
        </div>

    </form>

</div>

@endsection
