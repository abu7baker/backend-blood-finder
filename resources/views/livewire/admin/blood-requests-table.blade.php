<div wire:poll.10s>
    {{-- ====================== الإحصائيات ====================== --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">الطلبات الحرجة</small>
                        <h3 class="fw-bold text-danger">{{ $stats['critical'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-danger"><i class="fas fa-heart-pulse"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">قيد المراجعة</small>
                        <h3 class="fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-warning"><i class="fas fa-clock"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">مكتملة</small>
                        <h3 class="fw-bold text-success">{{ $stats['completed'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-success"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>

    </div>

    {{-- ====================== الجدول ====================== --}}
    <div class="card custom-card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>
                <i class="fas fa-file-medical text-danger ms-2"></i>
                طلبات الدم
            </h5>

            <div class="d-flex gap-2">

                {{-- زر إضافة طلب جديد (مودال) --}}
                <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                    <i class="fas fa-plus ms-2"></i> إضافة طلب جديد
                </button>

                {{-- الفلاتر --}}
                <div class="d-flex gap-2">
                    <select wire:model="status" class="form-select form-select-sm">
                        <option value="all">كل الحالات</option>
                        <option value="pending">قيد المراجعة</option>
                        <option value="approved">مقبول</option>
                        <option value="in_progress">جاري اكتمال عملية التبرع</option>
                        <option value="rejected">مرفوض</option>
                        <option value="completed">مكتمل</option>
                    </select>

                    <select wire:model="priority" class="form-select form-select-sm">
                        <option value="all">كل الأولويات</option>
                        <option value="normal">عادي</option>
                        <option value="urgent">عاجل</option>
                        <option value="critical">حرج</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body">

            <div class="mb-3">
                <input
                    type="text"
                    class="form-control"
                    placeholder="🔍 البحث..."
                    wire:model.debounce.400ms="search"
                >
            </div>

            <div class="table-responsive">
                <table class="table data-table" id="requestsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب / المريض</th>
                            <th>المستشفى</th>
                            <th>الفصيلة</th>
                            <th>الوحدات</th>
                            <th>الأولوية</th>
                            <th>الحالة</th>
                            <th>تاريخ الطلب</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($requests as $req)
                            <tr wire:key="request-{{ $req->id }}">
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>
                                        {{ $req->patient_name ?? ($req->requester->full_name ?? '—') }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ optional($req->requester)->role_id == '2' ? 'طلب من مستشفى' : 'طلب من مستخدم' }}
                                    </small>
                                </td>

                                <td>{{ optional($req->hospital)->name ?? '—' }}</td>
                                <td>{{ $req->blood_type }}</td>
                                <td>{{ $req->units_requested }}</td>

                                <td>
                                    @if($req->priority == 'critical')
                                        <span class="badge bg-danger">حرج</span>
                                    @elseif($req->priority == 'urgent')
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
                                        <span class="badge bg-primary">جاري اكتمال عملية التبرع</span>
                                    @elseif($req->status == 'rejected')
                                        <span class="badge bg-danger">مرفوض</span>
                                    @else
                                        <span class="badge bg-success">مكتمل</span>
                                    @endif
                                </td>

                                <td>{{ $req->created_at->format('Y-m-d') }}</td>

                                <td>
                                    <div class="btn-group btn-group-sm">

                                        {{-- تفاصيل --}}
                                        <button class="btn btn-outline-primary"
                                                type="button"
                                                onclick="viewRequest({{ $req->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- تعديل --}}
                                        <button class="btn btn-outline-success"
                                                type="button"
                                                onclick="editRequest({{ $req->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- حالة --}}
                                        <button class="btn btn-outline-warning"
                                                type="button"
                                                onclick="editStatus({{ $req->id }}, '{{ $req->status }}')">
                                            <i class="fas fa-sync"></i>
                                        </button>

                                        {{-- سجل الحالات --}}
                                        <button class="btn btn-outline-dark"
                                                type="button"
                                                onclick="loadHistory({{ $req->id }})">
                                            <i class="fas fa-history"></i>
                                        </button>

                                        {{-- حذف --}}
                                        <button class="btn btn-outline-danger"
                                                type="button"
                                                onclick="deleteRequest({{ $req->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">لا توجد طلبات.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
