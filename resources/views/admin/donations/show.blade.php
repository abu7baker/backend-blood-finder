<div class="table-responsive">
    <table class="table table-bordered">

        {{-- المتبرع --}}
        <tr>
            <th style="width: 30%">المتبرع</th>
            <td>
                <strong>{{ $donation->donor->full_name }}</strong><br>
                <small class="text-muted">
                    <i class="fas fa-phone"></i> {{ $donation->donor->phone }}
                </small>
            </td>
        </tr>

        {{-- الفصيلة --}}
        <tr>
            <th>الفصيلة</th>
            <td>
                <span class="badge bg-danger">{{ $donation->blood_type }}</span>
            </td>
        </tr>

        {{-- المستشفى --}}
        <tr>
            <th>المستشفى</th>
            <td>{{ $donation->hospital->name ?? '—' }}</td>
        </tr>

        {{-- نوع التبرع --}}
        <tr>
            <th>نوع التبرع</th>
            <td>
                @if($donation->request_id)
                    <span class="badge bg-primary">
                        تبرع عبر طلب دم (رقم الطلب: #{{ $donation->request_id }})
                    </span>
                @else
                    <span class="badge bg-info text-dark">
                        تبرع مباشر عبر موعد في المستشفى
                    </span>
                @endif
            </td>
        </tr>

        {{-- الوحدات --}}
        <tr>
            <th>الوحدات المتبرع بها</th>
            <td>{{ $donation->units_donated }}</td>
        </tr>

        {{-- حالة التبرع --}}
        <tr>
            <th>الحالة</th>
            <td>
                @switch($donation->status)
                    @case('willing')
                        <span class="badge bg-info text-dark">موافقة</span>
                        @break

                    @case('pending')
                        <span class="badge bg-warning text-dark">قيد التنفيذ</span>
                        @break

                    @case('completed')
                        <span class="badge bg-success">مكتمل</span>
                        @break

                    @default
                        <span class="badge bg-danger">ملغى</span>
                @endswitch
            </td>
        </tr>

        {{-- التاريخ --}}
        <tr>
            <th>تاريخ التبرع</th>
            <td>
                {{ \Carbon\Carbon::parse($donation->donated_at)->format('Y-m-d h:i A') }}
            </td>
        </tr>

    </table>
</div>
