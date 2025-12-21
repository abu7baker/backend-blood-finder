@extends('layouts.auth')

@section('title', 'إنشاء حساب مستشفى - المراجعة')

@section('content')

<div class="auth-card shadow-lg rounded-4 bg-white">

    {{-- العنوان --}}
    <div class="wizard-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <span class="small fw-bold">2 / 2</span>
            <h4 class="fw-bold">إنشاء حساب جديد</h4>
        </div>

        <div class="progress mt-3" style="height: 4px;">
            <div class="progress-bar bg-danger" style="width: 100%;"></div>
        </div>
    </div>

    {{-- مراجعة البيانات --}}
    <h5 class="fw-bold text-center mb-4">مراجعة وتأكيد</h5>

    <div class="border rounded p-3 bg-light">

        <p><strong>اسم المستشفى:</strong> {{ $data['name'] }}</p>
        <p><strong>البريد الإلكتروني:</strong> {{ $data['email'] }}</p>
        <p><strong>الهاتف:</strong> {{ $data['phone'] }}</p>
        <p><strong>المدينة:</strong> {{ $data['city'] }}</p>
        <p><strong>العنوان:</strong> {{ $data['location'] }}</p>

    </div>

    <div class="alert alert-warning mt-3 small">
        <i class="bi bi-exclamation-triangle"></i>
        سيتم مراجعة طلبك من قبل فريق الإدارة خلال 24 ساعة.
        ستستلم إشعارًا بالموافقة أو الرفض.
    </div>

    <form action="{{ route('hospital.register.step2.post') }}" method="POST">
        @csrf

        <div class="form-check mt-3 mb-3">
            <input class="form-check-input" type="checkbox" required id="agreeCheck">
            <label class="form-check-label" for="agreeCheck">
                أوافق على الشروط والأحكام وسياسة الخصوصية
            </label>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('hospital.register.step1') }}" class="btn btn-secondary px-4">السابق</a>
            <button type="submit" class="btn btn-danger px-4">إنشاء الحساب</button>
        </div>

    </form>

</div>

@endsection
