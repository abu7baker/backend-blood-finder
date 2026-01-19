<!-- Recent Alerts -->
<div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-triangle-exclamation text-danger ms-2"></i>
            التنبيهات الحديثة
        </h5>
        <button class="btn btn-sm btn-outline-danger" type="button">عرض الكل</button>
    </div>
    <div class="card-body">
        <div class="alerts-container">
            @forelse ($recentNotifications as $note)
                @php
                    $type = $note->type ?? 'general';
                    $styles = [
                        'blood_request_donor_alert' => ['tone' => 'warning', 'icon' => 'fa-triangle-exclamation', 'label' => 'طارئ'],
                        'stock_alert' => ['tone' => 'warning', 'icon' => 'fa-boxes-stacked', 'label' => 'مخزون'],
                        'donor_alert' => ['tone' => 'info', 'icon' => 'fa-bell', 'label' => 'تنبيه'],
                        'donor_accepted' => ['tone' => 'success', 'icon' => 'fa-heart', 'label' => 'متبرع'],
                        'new_blood_request' => ['tone' => 'info', 'icon' => 'fa-droplet', 'label' => 'طلب جديد'],
                        'blood_request' => ['tone' => 'info', 'icon' => 'fa-droplet', 'label' => 'طلب'],
                        'blood_request_cancelled' => ['tone' => 'secondary', 'icon' => 'fa-ban', 'label' => 'ملغي'],
                        'appointment_created' => ['tone' => 'info', 'icon' => 'fa-calendar-check', 'label' => 'موعد'],
                        'appointment_approved' => ['tone' => 'success', 'icon' => 'fa-circle-check', 'label' => 'تمت الموافقة'],
                        'appointment_rejected' => ['tone' => 'danger', 'icon' => 'fa-circle-xmark', 'label' => 'مرفوض'],
                        'appointment_completed' => ['tone' => 'success', 'icon' => 'fa-flag-checkered', 'label' => 'مكتمل'],
                        'single' => ['tone' => 'info', 'icon' => 'fa-bell', 'label' => 'تنبيه'],
                        'broadcast' => ['tone' => 'info', 'icon' => 'fa-bullhorn', 'label' => 'إعلان'],
                    ];
                    $style = $styles[$type] ?? ['tone' => 'warning', 'icon' => 'fa-bell', 'label' => 'تنبيه'];
                @endphp
                <div class="alert-item alert-{{ $style['tone'] }}">
                    <div class="alert-icon bg-{{ $style['tone'] }}">
                        <i class="fas {{ $style['icon'] }}"></i>
                    </div>
                    <div class="alert-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <p class="mb-0 fw-medium">{{ $note->title }}</p>
                            <span class="badge bg-{{ $style['tone'] }}">{{ $style['label'] }}</span>
                        </div>
                        <div class="d-flex gap-3 text-muted small">
                            <span><i class="fas fa-user ms-1"></i>{{ $note->user->full_name ?? 'النظام' }}</span>
                            <span>{{ $note->created_at?->diffForHumans() }}</span>
                        </div>
                        @if(!empty($note->body))
                            <div class="text-muted small mt-1">{{ $note->body }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center mb-0">
                    لا توجد تنبيهات حالياً.
                </div>
            @endforelse
        </div>
    </div>
</div>
