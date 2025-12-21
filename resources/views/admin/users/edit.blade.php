@extends('layouts.admin')

@section('title', 'تعديل المستخدم')

@section('content')

<main id="mainContent" class="main-content">

    <div class="content-wrapper">

        <div class="card custom-card">

            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-edit text-success ms-2"></i>
                    تعديل المستخدم
                </h5>

                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-arrow-right ms-1"></i> العودة
                </a>
            </div>

            <div class="card-body">

             <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <label>الاسم الكامل</label>
    <input type="text" name="full_name" value="{{ $user->full_name }}" class="form-control">

    <label>البريد</label>
    <input type="email" name="email" value="{{ $user->email }}" class="form-control">

    <label>الهاتف</label>
    <input type="text" name="phone" value="{{ $user->phone }}" class="form-control">

    <label>المدينة</label>
    <input type="text" name="city" value="{{ $user->city }}" class="form-control">

    <label>فصيلة الدم</label>
    <input type="text" name="blood_type" value="{{ $user->blood_type }}" class="form-control">

    <label>الدور</label>
    <select name="role_id" class="form-select">
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected':'' }}>
                {{ $role->name }}
            </option>
        @endforeach
    </select>

    <label>الحالة</label>
    <select name="status" class="form-select">
        <option value="active"  {{ $user->status=='active' ? 'selected':'' }}>نشط</option>
        <option value="pending" {{ $user->status=='pending' ? 'selected':'' }}>قيد المراجعة</option>
        <option value="blocked" {{ $user->status=='blocked' ? 'selected':'' }}>محظور</option>
    </select>

    <label>كلمة المرور (اختياري)</label>
    <input type="password" name="password" class="form-control">

    <button type="submit" class="btn btn-primary mt-3">حفظ التعديلات</button>
</form>

            </div>

        </div>

    </div>

</main>

@endsection
