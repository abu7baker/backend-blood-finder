<div class="dropdown" wire:poll.3s="poll">
    <button class="btn btn-light position-relative d-flex align-items-center justify-content-center"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="الإشعارات">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end p-0 notification-dropdown">
        <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-semibold">الإشعارات</span>
            <a href="{{ route('hospital.notifications.index') }}" class="small text-decoration-none">
                عرض الكل
            </a>
        </div>

        <div class="list-group list-group-flush">
            @forelse($notifications as $note)
                @php
                    $styles = [
                        'blood_request_donor_alert' => ['tone' => 'warning', 'icon' => 'fa-triangle-exclamation'],
                        'stock_alert' => ['tone' => 'warning', 'icon' => 'fa-boxes-stacked'],
                        'donor_alert' => ['tone' => 'info', 'icon' => 'fa-bell'],
                        'donor_accepted' => ['tone' => 'success', 'icon' => 'fa-heart'],
                        'new_blood_request' => ['tone' => 'info', 'icon' => 'fa-droplet'],
                        'blood_request' => ['tone' => 'danger', 'icon' => 'fa-droplet'],
                        'blood_request_cancelled' => ['tone' => 'secondary', 'icon' => 'fa-ban'],
                        'appointment_created' => ['tone' => 'info', 'icon' => 'fa-calendar-check'],
                        'appointment_approved' => ['tone' => 'success', 'icon' => 'fa-circle-check'],
                        'appointment_rejected' => ['tone' => 'danger', 'icon' => 'fa-circle-xmark'],
                        'appointment_completed' => ['tone' => 'success', 'icon' => 'fa-flag-checkered'],
                        'broadcast' => ['tone' => 'info', 'icon' => 'fa-bullhorn'],
                        'single' => ['tone' => 'info', 'icon' => 'fa-bell'],
                        'general' => ['tone' => 'secondary', 'icon' => 'fa-bell'],
                    ];
                    $style = $styles[$note['type'] ?? 'general'] ?? ['tone' => 'secondary', 'icon' => 'fa-bell'];
                @endphp

                <a href="{{ route('hospital.notifications.show', $note['id']) }}"
                    class="list-group-item list-group-item-action d-flex align-items-start notification-item {{ $note['is_read'] ? '' : 'notification-unread' }}"
                    wire:key="notification-{{ $note['id'] }}">
                    <div class="notification-icon bg-{{ $style['tone'] }}">
                        <i class="fas {{ $style['icon'] }}"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold">{{ $note['title'] }}</div>
                        @if(!empty($note['body']))
                            <div class="text-muted small">
                                {{ \Illuminate\Support\Str::limit($note['body'], 80) }}
                            </div>
                        @endif
                        <div class="text-muted small">{{ $note['created_at'] }}</div>
                    </div>
                    @unless($note['is_read'])
                        <span class="badge bg-danger align-self-center">جديد</span>
                    @endunless
                </a>
            @empty
                <div class="px-3 py-3 text-center text-muted small">
                    لا توجد إشعارات حالياً.
                </div>
            @endforelse
        </div>
    </div>
</div>
