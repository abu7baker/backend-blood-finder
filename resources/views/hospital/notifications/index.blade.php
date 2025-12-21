@extends('hospital.layouts.hospital')

@section('title', 'الإشعارات')

@section('content')
    <main id="mainContent" class="main-content">
        <div class="content-wrapper">

            {{-- ===================== العنوان + زر تحديد الكل ===================== --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">
                    <i class="fas fa-bell text-danger ms-2"></i> الإشعارات
                </h4>

                @if($unreadCount > 0)
                    <form action="{{ route('hospital.notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-check-double ms-1"></i> تحديد الكل كمقروء
                        </button>
                    </form>
                @endif
            </div>


            {{-- ===================== في حالة عدم وجود إشعارات ===================== --}}
            @if($notifications->isEmpty())
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0">لا توجد إشعارات حتى الآن.</p>
                </div>
            @else

                {{-- ===================== قائمة الإشعارات ===================== --}}
                <div class="notification-list">

                    @foreach($notifications as $note)

                        @php
                            // تحديد اللون والأيقونة حسب نوع الإشعار
                            $typeIcon = [
                                'request' => 'fa-hand-holding-medical',
                                'appointment' => 'fa-calendar-check',
                                'general' => 'fa-bell',
                            ];

                            $typeColor = [
                                'request' => 'text-danger',
                                'appointment' => 'text-primary',
                                'general' => 'text-secondary',
                            ];

                            $icon = $typeIcon[$note->type] ?? 'fa-bell';
                            $color = $typeColor[$note->type] ?? 'text-secondary';
                        @endphp

                        <a href="{{ route('hospital.notifications.show', $note->id) }}"
                            class="notification-card d-flex align-items-start p-3 mb-3 rounded shadow-sm {{ $note->is_read ? 'bg-white' : 'bg-light border-start border-4 border-danger' }}"
                            style="text-decoration: none;">

                            <div class="me-3">
                                <div class="icon-circle {{ $color }}">
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                            </div>

                            <div class="flex-fill">
                                <h6 class="fw-bold mb-1 {{ $note->is_read ? 'text-dark' : 'text-danger' }}">
                                    {{ $note->title }}
                                </h6>
                                <p class="text-muted small mb-1">{{ $note->body }}</p>

                                <small class="text-secondary">
                                    <i class="fas fa-clock ms-1"></i>
                                    {{ $note->created_at->diffForHumans() }}
                                </small>
                            </div>

                            {{-- نقطة حمراء لغير المقروء --}}
                            @unless($note->is_read)
                                <span class="badge bg-danger rounded-pill align-self-center ms-3">
                                    جديد
                                </span>
                            @endunless

                        </a>

                    @endforeach

                </div>

            @endif

        </div>
    </main>

    <style>
        .notification-card:hover {
            background: #f8f9fa !important;
            transform: translateX(-3px);
            transition: 0.2s ease;
        }

        .icon-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
        }
    </style>

@endsection