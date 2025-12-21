@if($history->isEmpty())
    <p class="text-muted text-center">لا يوجد سجل تغييرات حتى الآن.</p>
@else

    @php
        // ================= ترجمة الحالات =================
        $statusMap = [
            'pending'   => 'قيد المراجعة',
            'approved'  => 'مقبول',
            'rejected'  => 'مرفوض',
            'completed' => 'مكتمل',
            'canceled'  => 'ملغي',
            'willing'   => 'موافقة',
        ];

        // ================= ألوان البادج =================
        $badgeColors = [
            'pending'   => 'bg-warning text-dark',
            'approved'  => 'bg-info text-dark',
            'rejected'  => 'bg-danger',
            'completed' => 'bg-success',
            'canceled'  => 'bg-secondary',
            'willing'   => 'bg-primary',
        ];
    @endphp

    <ul class="list-group">

        @foreach($history as $item)
            <li class="list-group-item">

                <div class="d-flex justify-content-between">
                    <div>

                        {{-- الحالة القديمة --}}
                        <strong>من:</strong>
                        <span class="badge {{ $badgeColors[$item->old_status] ?? 'bg-dark' }}">
                            {{ $statusMap[$item->old_status] ?? $item->old_status }}
                        </span>

                        {{-- الحالة الجديدة --}}
                        <strong class="ms-2">إلى:</strong>
                        <span class="badge {{ $badgeColors[$item->new_status] ?? 'bg-dark' }}">
                            {{ $statusMap[$item->new_status] ?? $item->new_status }}
                        </span>

                    </div>

                    <small class="text-muted">
                        {{ $item->created_at->format('Y-m-d h:i A') }}
                    </small>
                </div>

                {{-- المستخدم المسؤول عن التغيير --}}
                <div class="mt-2">
                    <strong>تم بواسطة:</strong>
                    {{ optional($item->changer)->full_name ?? '—' }}
                </div>

                {{-- ملاحظة اختيارية --}}
                @if($item->comment)
                    <div class="mt-2 text-muted">
                        <strong>ملاحظة:</strong> {{ $item->comment }}
                    </div>
                @endif

            </li>
        @endforeach

    </ul>
@endif
