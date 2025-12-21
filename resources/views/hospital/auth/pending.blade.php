@extends('layouts.auth')

@section('title', 'حسابك قيد المراجعة')

@section('content')


<div class="auth-card shadow-lg rounded-4 bg-white text-center">

    <div class="mb-4">
        <div class="mb-3">
            <i class="bi bi-hourglass-split text-warning" style="font-size: 3rem;"></i>
        </div>
        <h3 class="fw-bold text-danger">حسابك قيد المراجعة</h3>
        <p class="text-muted">شكرًا لتسجيلك في منصة Blood Finder. حسابك حاليًا تحت المراجعة.</p>
    </div>

    <div class="text-start bg-light p-3 rounded">

        <p>
            <i class="bi bi-check-circle-fill text-success"></i>
            <strong>تم إرسال طلب التسجيل:</strong> بنجاح
        </p>

        <p>
            <i class="bi bi-hourglass-split text-warning"></i>
            <strong>مراجعة البيانات:</strong> جاري المراجعة…
        </p>

        <p>
            <i class="bi bi-circle text-secondary"></i>
            <strong>الموافقة النهائية:</strong> في انتظار الإدارة
        </p>

        <div class="alert alert-info small mt-3">
            عادة ما تستغرق عملية المراجعة من 12 إلى 24 ساعة عمل.
            ستستلم إشعارًا فور الموافقة على حسابك.
        </div>

        <p class="mt-3">
                <strong>سنتواصل معك عبر البريد:</strong><br>
                {{ $user->email }}
            </p>


    </div>

    <div class="mt-4 text-center">
    <form action="{{ route('hospital.checkStatus') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            تحديث الحالة <i class="bi bi-arrow-repeat ms-1"></i>
        </button>
    </form>
</div>


    <div class="d-flex justify-content-center mt-4">
      

     <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            تحديث الحالة <i class="bi bi-arrow-repeat ms-1"></i>
        </button>
    </form>
    </div>

   
</div>

@endsection
