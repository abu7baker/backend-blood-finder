@extends('layouts.admin')

@section('title', 'إدارة طلبات الدم')

@section('content')
<main id="mainContent" class="main-content">
    <div class="content-wrapper">

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
                    <form method="GET" class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>كل الحالات</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                            <option value="in_progress">جاري اكتمال عملية التبرع</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                        </select>

                        <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>كل الأولويات</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>عادي</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                            <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>حرج</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <input type="text" id="searchRequests" class="form-control" placeholder="🔍 البحث...">
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
                                <tr>
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

        {{-- ====================== مودال التفاصيل ====================== --}}
        <div class="modal fade" id="viewRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle me-2"></i>
                            تفاصيل الطلب
                        </h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="text-muted small">نوع الطلب</label>
                                <div id="viewType" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">الحالة</label>
                                <div id="viewStatus" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">المستشفى</label>
                                <div id="viewHospital" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">الفصيلة / الوحدات</label>
                                <div id="viewBlood" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">اسم المريض</label>
                                <div id="viewPatientName" class="fw-bold"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="text-muted small">العمر</label>
                                <div id="viewPatientAge" class="fw-bold"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="text-muted small">الجنس</label>
                                <div id="viewPatientGender" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">اسم الطبيب</label>
                                <div id="viewDoctor" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">التشخيص</label>
                                <div id="viewDiag" class="fw-bold"></div>
                            </div>

                            <div class="col-12">
                                <label class="text-muted small">ملاحظات</label>
                                <div id="viewNotes" class="fw-bold"></div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ====================== مودال تعديل الطلب ====================== --}}
        <div class="modal fade" id="editRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <form id="editRequestForm" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i>
                                تعديل الطلب
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">الوحدات المطلوبة</label>
                                    <input type="number" name="units_requested" id="editUnits" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الأولوية</label>
                                    <select name="priority" id="editPriority" class="form-select">
                                        <option value="normal">عادي</option>
                                        <option value="urgent">عاجل</option>
                                        <option value="critical">حرج</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea name="notes" id="editNotes" class="form-control"></textarea>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="submit" class="btn btn-success">حفظ التعديلات</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== مودال تغيير الحالة ====================== --}}
        <div class="modal fade" id="editStatusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="editStatusForm" method="POST">
                        @csrf

                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-sync me-2"></i>
                                تحديث الحالة
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <label class="form-label">اختر الحالة الجديدة</label>
                            <select class="form-select" name="status" id="editStatusSelect" required>
                                <option value="pending">قيد المراجعة</option>
                                <option value="approved">مقبول</option>
                                <option value="in_progress">جاري اكتمال عملية التبرع</option>
                                <option value="rejected">مرفوض</option>
                                <option value="completed">مكتمل</option>
                            </select>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="submit" class="btn btn-warning text-white">حفظ</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== مودال سجل الحالات ====================== --}}
        <div class="modal fade" id="historyModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-history me-2"></i>
                            سجل التغييرات
                        </h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div id="historyBody">تحميل...</div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ====================== مودال الحذف ====================== --}}
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-trash me-2"></i>
                                حذف الطلب
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>هل أنت متأكد أنك تريد حذف هذا الطلب؟</p>
                        </div>

                        <div class="modal-footer bg-light">
                            <button class="btn btn-danger">حذف الآن</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== مودال إنشاء طلب جديد ====================== --}}
        <div class="modal fade" id="createRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <form id="createRequestForm" action="{{ route('admin.requests.store') }}" method="POST">
                        @csrf

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-plus me-2"></i>
                                إضافة طلب دم جديد
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">المريض</label>
                                    <input type="text" name="patient_name" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">العمر</label>
                                    <input type="number" name="patient_age" class="form-control" min="1" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">الجنس</label>
                                    <select name="patient_gender" class="form-select" required>
                                        <option value="M">ذكر</option>
                                        <option value="F">أنثى</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">المستشفى</label>
                                    <select name="hospital_id" class="form-select" required>
                                        @foreach($hospitals as $h)
                                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">الفصيلة</label>
                                    <select name="blood_type" class="form-select" required>
                                        <option>O+</option><option>O-</option>
                                        <option>A+</option><option>A-</option>
                                        <option>B+</option><option>B-</option>
                                        <option>AB+</option><option>AB-</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">الوحدات</label>
                                    <input type="number" name="units_requested" class="form-control" min="1" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">الأولوية</label>
                                    <select name="priority" class="form-select" required>
                                        <option value="normal">عادي</option>
                                        <option value="urgent">عاجل</option>
                                        <option value="critical">حرج</option>
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">التشخيص</label>
                                    <input type="text" name="diagnosis" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">ملاحظات إضافية</label>
                                    <textarea name="notes" class="form-control"></textarea>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</main>

{{-- ================================================================= --}}
{{-- =================== JavaScript Section ============================ --}}
{{-- ================================================================= --}}
@push('scripts')
<script>
    // بحث بسيط داخل الجدول
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRequests');
        const table       = document.getElementById('requestsTable');

        if (searchInput && table) {
            searchInput.addEventListener('keyup', function () {
                const term = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        }
    });

    // فتح مودال إنشاء طلب جديد
    function openCreateModal() {
        new bootstrap.Modal(document.getElementById('createRequestModal')).show();
    }

    // عرض تفاصيل الطلب
    function viewRequest(id) {
        fetch(`/admin/blood-requests/${id}/json`)
            .then(res => res.json())
            .then(req => {
                const isHospital = req.requester && req.requester.role === 'hospital';

                document.getElementById('viewType').innerText =
                    isHospital ? "طلب من مستشفى" : "طلب من مستخدم";

                document.getElementById('viewStatus').innerText    = req.status ?? '—';
                document.getElementById('viewHospital').innerText  = (req.hospital && req.hospital.name) ? req.hospital.name : '—';
                document.getElementById('viewBlood').innerText     = `${req.blood_type ?? ''} / ${req.units_requested ?? 0}`;
                document.getElementById('viewPatientName').innerText   = req.patient_name ?? '—';
                document.getElementById('viewPatientAge').innerText    = req.patient_age ?? '—';
                document.getElementById('viewPatientGender').innerText = req.patient_gender ?? '—';
                document.getElementById('viewDoctor').innerText        = req.doctor_name ?? '—';
                document.getElementById('viewDiag').innerText          = req.diagnosis ?? '—';
                document.getElementById('viewNotes').innerText         = req.notes ?? '—';

                new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
            })
            .catch(() => alert('حدث خطأ أثناء جلب بيانات الطلب'));
    }

    // فتح مودال تعديل الطلب مع تعبئة البيانات
    function editRequest(id) {
        fetch(`/admin/blood-requests/${id}/json`)
            .then(res => res.json())
            .then(req => {
                document.getElementById('editUnits').value    = req.units_requested ?? '';
                document.getElementById('editPriority').value = req.priority ?? 'normal';
                document.getElementById('editNotes').value    = req.notes ?? '';

                document.getElementById('editRequestForm').action =
                    `/admin/blood-requests/${id}`;

                new bootstrap.Modal(document.getElementById('editRequestModal')).show();
            })
            .catch(() => alert('حدث خطأ أثناء جلب بيانات الطلب'));
    }

    // فتح مودال تعديل الحالة
    function editStatus(id, currentStatus) {
        document.getElementById('editStatusSelect').value = currentStatus;
        document.getElementById('editStatusForm').action  =
            `/admin/blood-requests/${id}/status`;

        new bootstrap.Modal(document.getElementById('editStatusModal')).show();
    }

    // فتح مودال الحذف
    function deleteRequest(id) {
        document.getElementById('deleteForm').action =
            `/admin/blood-requests/${id}`;

        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // تحميل سجل الحالات وعرضه في المودال
    function loadHistory(id) {
        fetch(`/admin/blood-requests/${id}/history`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('historyBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('historyModal')).show();
            })
            .catch(() => alert('تعذر تحميل سجل الحالات'));
    }
</script>
@endpush
@endsection
