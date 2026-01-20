<div wire:poll.10s>
    @unless($hasHospital)
        <div class="alert alert-danger mb-3">
            لا يمكن عرض الطلبات لأن الحساب غير مرتبط بمستشفى.
        </div>
    @endunless

    {{-- ========================= الإحصائيات ========================= --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">الطلبات العاجلة</small>
                        <h3 class="fw-bold text-danger">{{ $stats['urgent'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">قيد المراجعة</small>
                        <h3 class="fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">مكتملة</small>
                        <h3 class="fw-bold text-success">{{ $stats['completed'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================= جدول الطلبات ========================= --}}
    <div class="card custom-card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold">
                <i class="fas fa-file-medical text-danger ms-2"></i>
                طلبات الدم
            </h5>
            <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                <i class="fas fa-plus ms-2"></i> إضافة طلب جديد
            </button>
        </div>

        <div class="card-body">

            <div class="mb-3">
                <input
                    type="text"
                    class="form-control"
                    placeholder="ابحث عن طلب..."
                    wire:model.debounce.400ms="search"
                >
            </div>

            <div class="table-responsive">
                <table class="table data-table table-hover" id="requestsTable">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المريض</th>
                            <th>الفصيلة</th>
                            <th>الوحدات</th>
                            <th>الأولوية</th>
                            <th>الحالة</th>
                            <th>تاريخ الطلب</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($requests as $req)
                            <tr wire:key="hospital-request-{{ $req->id }}">
                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-bold">
                                    @if($req->patient_name)
                                        {{ $req->patient_name }}
                                    @else
                                        {{ $req->requester->full_name }}
                                        <small class="text-muted">(طلب شخصي)</small>
                                    @endif
                                </td>

                                <td><span class="badge bg-danger">{{ $req->blood_type }}</span></td>
                                <td>{{ $req->units_requested }}</td>

                                <td>
                                    @if($req->priority == 'urgent')
                                        <span class="badge bg-warning text-dark">عاجل</span>
                                    @else
                                        <span class="badge bg-secondary">عادي</span>
                                    @endif
                                </td>

                                <td>
                                    @if($req->status == 'pending')
                                        <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                    @elseif($req->status == 'approved')
                                        <span class="badge bg-info text-dark">مقبول</span>
                                    @elseif($req->status == 'in_progress')
                                        <span class="badge bg-primary">جاري اكتمال التبرع</span>
                                    @elseif($req->status == 'rejected')
                                        <span class="badge bg-danger">مرفوض</span>
                                    @else
                                        <span class="badge bg-success">مكتمل</span>
                                    @endif
                                </td>

                                <td>{{ $req->created_at->format('Y-m-d') }}</td>

                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewRequest({{ $req->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn btn-outline-warning"
                                            onclick="openStatusModal({{ $req->id }}, '{{ $req->status }}')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">لا توجد طلبات.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
