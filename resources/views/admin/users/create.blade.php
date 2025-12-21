@extends('layouts.admin')

@section('title', 'إضافة مستخدم')

@section('content')

<main id="mainContent" class="main-content">

    <div class="content-wrapper">

        <div class="card custom-card">

            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus text-success ms-2"></i>
                    إضافة مستخدم
                </h5>

                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-arrow-right ms-1"></i> العودة
                </a>
            </div>

            <div class="card-body">

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">الاسم الكامل *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">كلمة المرور *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">نوع المستخدم *</label>
                            <select class="form-select" name="user_type" required>
                                <option value="">اختر النوع</option>
                                <option value="admin">مدير</option>
                                <option value="hospital">مستشفى</option>
                                <option value="user">مستخدم</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">الحالة *</label>
                            <select class="form-select" name="status" required>
                                <option value="active">نشط</option>
                                <option value="pending">قيد المراجعة</option>
                                <option value="blocked">محظور</option>
                            </select>
                        </div>

                    </div>

                    <div class="mt-4 text-start">
                        <button class="btn btn-primary">
                            <i class="fas fa-save ms-2"></i> حفظ المستخدم
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</main>

@endsection
