@extends('layouts.admin')

@section('title', 'إدارة المخزون')

@section('content')
<main id="mainContent" class="main-content">

    <div class="content-wrapper">

        {{-- إحصائيات حسب الفصائل --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">فصيلة O+</small>
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['o_pos'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 
                                <!-- يمكن لاحقاً تحط نسبة تغيير -->
                            </small>
                        </div>
                        <div class="stat-icon bg-red">
                            <i class="fas fa-droplet"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">فصيلة A+</small>
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['a_pos'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="fas fa-arrow-down"></i>
                            </small>
                        </div>
                        <div class="stat-icon bg-blue">
                            <i class="fas fa-droplet"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">فصيلة B+</small>
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['b_pos'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i>
                            </small>
                        </div>
                        <div class="stat-icon bg-green">
                            <i class="fas fa-droplet"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">فصيلة AB+</small>
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['ab_pos'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-minus"></i>
                            </small>
                        </div>
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-droplet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- التنبيهات + الإحصائيات السريعة --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-triangle-exclamation text-danger ms-2"></i>
                            تنبيهات النقص الحاد
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse ($lowStocks as $low)
                            <div class="alert-item alert-warning mb-3">
                                <div class="alert-icon" style="background: #ffc107;">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                                <div class="flex-fill">
                                    <strong>{{ $low->hospital->name }} - {{ $low->hospital->city }}</strong>
                                    <p class="mb-0 small text-muted">
                                        فصيلة {{ $low->blood_type }} - متبقي {{ $low->units_available }} وحدات فقط
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">لا توجد تنبيهات نقص حاد حالياً.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-primary ms-2"></i>
                            إحصائيات سريعة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted">إجمالي الوحدات المتاحة</small>
                                <h4 class="mb-0 fw-bold">{{ $stats['total_available'] ?? 0 }}</h4>
                            </div>
                            <div class="stat-icon bg-blue" style="width: 50px; height: 50px;">
                                <i class="fas fa-droplet"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted">وحدات منتهية الصلاحية</small>
                                <h4 class="mb-0 fw-bold text-danger">{{ $stats['total_expired'] ?? 0 }}</h4>
                            </div>
                            <div class="stat-icon bg-red" style="width: 50px; height: 50px;">
                                <i class="fas fa-calendar-xmark"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">طلبات الدم النشطة</small>
                                <h4 class="mb-0 fw-bold text-success">{{ $stats['active_requests'] ?? 0 }}</h4>
                            </div>
                            <div class="stat-icon bg-green" style="width: 50px; height: 50px;">
                                <i class="fas fa-file-medical"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- جدول المخزون --}}
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table text-danger ms-2"></i>
                    مخزون المستشفيات
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success" onclick="exportCSV('inventoryTable', 'inventory')">
                        <i class="fas fa-file-excel ms-2"></i>تصدير
                    </button>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateInventoryModal">
                        <i class="fas fa-edit ms-2"></i>تحديث المخزون
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" id="searchInventory" class="form-control" placeholder="🔍 البحث...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterCity">
                            <option value="all">جميع المدن</option>
                            @php
                                $cities = $hospitals->pluck('city')->unique()->filter();
                            @endphp
                            @foreach ($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterBloodType">
                            <option value="all">جميع الفصائل</option>
                            @foreach ($bloodTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المستشفى</th>
                                <th>المدينة</th>
                                <th>O+</th>
                                <th>O-</th>
                                <th>A+</th>
                                <th>A-</th>
                                <th>B+</th>
                                <th>B-</th>
                                <th>AB+</th>
                                <th>AB-</th>
                                <th>الإجمالي</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $types = ['O+','O-','A+','A-','B+','B-','AB+','AB-'];
                            @endphp

                            @forelse ($hospitals as $index => $hospital)
                                @php
                                    $map = array_fill_keys($types, 0);
                                    foreach ($hospital->bloodStock as $stock) {
                                        if (isset($map[$stock->blood_type])) {
                                            $map[$stock->blood_type] += $stock->units_available;
                                        }
                                    }
                                    $total = array_sum($map);
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $hospital->name }}</strong></td>
                                    <td>{{ $hospital->city }}</td>

                                    @foreach ($types as $bt)
                                        @php $value = $map[$bt]; @endphp
                                        <td>
                                            @if($value > 0)
                                                @php
                                                    $badgeClass = $value <= 3 ? 'bg-danger' : ($value <= 7 ? 'bg-warning' : 'bg-success');
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $value }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td><strong>{{ $total }}</strong></td>
                                    <td>
                                       <button class="btn btn-sm btn-outline-primary" 
        onclick="viewInventory({{ $hospital->id }})"
        title="عرض التفاصيل">
    <i class="fas fa-eye"></i>
</button>


                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">
                                        لا توجد بيانات مخزون حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</main>

{{-- مودال تحديث المخزون --}}
<div class="modal fade" id="updateInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-primary ms-2"></i>
                    تحديث المخزون
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

       <form id="updateInventoryForm" method="POST" action="{{ route('admin.bloodstock.updateStock') }}">
    @csrf

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">المستشفى *</label>
                            <select class="form-select" name="hospital_id" required>
                                <option value="">اختر المستشفى</option>
                                @foreach ($hospitals as $hospital)
                                    <option value="{{ $hospital->id }}">{{ $hospital->name }} - {{ $hospital->city }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">فصيلة الدم *</label>
                            <select class="form-select" name="blood_type" required>
                                <option value="">اختر الفصيلة</option>
                                @foreach ($bloodTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">الكمية الجديدة *</label>
                            <input type="number" class="form-control" name="units_available" min="0" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" rows="3" name="notes"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save ms-2"></i>حفظ
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- مودال عرض تفاصيل المخزون -->
<div class="modal fade" id="viewInventoryModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> تفاصيل المخزون</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="inventoryDetails">
             ززز
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof searchTable === 'function') {
            searchTable('searchInventory', 'inventoryTable');
        }
        if (typeof filterTable === 'function') {
            filterTable('filterCity', 'inventoryTable', 2);
        }
        // يمكنك لاحقاً إضافة فلترة بالفصيلة من خلال JS مخصص

        
    });
    function viewInventory(id) {
    fetch(`/admin/blood-stock/details/${id}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('inventoryDetails').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewInventoryModal')).show();
        })
        .catch(err => console.error(err));
}

</script>
@endpush
