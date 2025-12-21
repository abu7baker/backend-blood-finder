@extends('hospital.layouts.hospital')

@section('title', 'مخزون الدم')

@section('content')

<main id="mainContent" class="main-content">
    <div class="content-wrapper">

        {{-- ======================== إحصائيات الفصائل ======================== --}}
        <div class="row g-4 mb-4">

            @foreach ($stats['by_type'] as $type => $count)
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">فصيلة {{ $type }}</small>
                                <h3 class="mb-0 fw-bold text-danger">{{ $count }}</h3>
                            </div>
                            <div class="stat-icon bg-red">
                                <i class="fas fa-droplet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- ======================== إحصائيات سريعة ======================== --}}
        <div class="row g-4 mb-4">

            {{-- إجمالي الوحدات --}}
            <div class="col-lg-6">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie text-primary ms-2"></i> إحصائيات سريعة</h5>
                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted">إجمالي الوحدات المتاحة</small>
                                <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="stat-icon bg-blue" style="width:50px;height:50px;">
                                <i class="fas fa-droplet"></i>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted">وحدات منتهية الصلاحية</small>
                                <h4 class="mb-0 fw-bold text-danger">{{ $stats['expired'] }}</h4>
                            </div>
                            <div class="stat-icon bg-red" style="width:50px;height:50px;">
                                <i class="fas fa-calendar-xmark"></i>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">طلبات الدم النشطة</small>
                                <h4 class="mb-0 fw-bold text-success">{{ $stats['active_requests'] }}</h4>
                            </div>
                            <div class="stat-icon bg-green" style="width:50px;height:50px;">
                                <i class="fas fa-file-medical"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- تنبيهات النقص --}}
            <div class="col-lg-6">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-triangle-exclamation text-danger ms-2"></i> تنبيهات النقص</h5>
                    </div>

                    <div class="card-body">

                        @if (count($stats['low_stock']))
                            @foreach ($stats['low_stock'] as $item)
                                <div class="alert-item alert-warning mb-3">
                                    <div class="alert-icon" style="background:#ffc107;">
                                        <i class="fas fa-triangle-exclamation"></i>
                                    </div>
                                    <div class="flex-fill">
                                        <strong>فصيلة {{ $item['type'] }}</strong>
                                        <p class="mb-0 small text-muted">متبقي {{ $item['count'] }} وحدات فقط</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">لا يوجد نقص حالياً.</p>
                        @endif

                    </div>
                </div>
            </div>

        </div>

        {{-- ======================== جدول المخزون ======================== --}}
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table text-danger ms-2"></i> مخزون المستشفى</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <input type="text" class="form-control" id="searchStock" placeholder="🔍 البحث...">
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="stockTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>فصيلة الدم</th>
                                <th>الوحدات المتوفرة</th>
                                <th>تاريخ آخر تحديث</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($stocks as $i => $s)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $s->blood_type }}</td>
                                    <td>
                                        <span class="badge 
                                            {{ $s->units_available <= 3 ? 'bg-danger' : 
                                            ($s->units_available <= 7 ? 'bg-warning' : 'bg-success') }}">
                                            {{ $s->units_available }}
                                        </span>
                                    </td>
                                    <td>{{ $s->updated_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach

                            @if ($stocks->count() == 0)
                                <tr>
                                    <td colspan="4" class="text-center text-muted">لا توجد بيانات مخزون بعد.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</main>

@endsection

@push('scripts')
<script>
    document.getElementById('searchStock').addEventListener('keyup', function () {
        let value = this.value.toLowerCase();
        document.querySelectorAll('#stockTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>
@endpush
