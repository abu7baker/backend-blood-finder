@extends('hospital.layouts.hospital')

@section('title', 'تفاصيل الإشعار')

@section('content')
<main id="mainContent" class="main-content">
    <div class="content-wrapper">

        <div class="card custom-card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bell ms-2"></i>
                    تفاصيل الإشعار
                </h5>
            </div>

            <div class="card-body">

                <h4 class="fw-bold mb-3">{{ $notification->title }}</h4>

                <p class="text-muted mb-4">
                    {{ $notification->body }}
                </p>

                <div class="d-flex justify-content-between text-muted">
                    <small>
                        <i class="fas fa-clock ms-1"></i>
                        وقت الإرسال: {{ $notification->created_at->format('Y-m-d h:i A') }}
                    </small>

                    <small>
                        @if($notification->is_read)
                            <span class="text-success">تمت قراءته</span>
                        @else
                            <span class="text-danger">غير مقروء</span>
                        @endif
                    </small>
                </div>

            </div>

        </div>

        <div class="mt-4">
            <a href="{{ route('hospital.notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right ms-1"></i>
                رجوع إلى الإشعارات
            </a>
        </div>

    </div>
</main>
@endsection
