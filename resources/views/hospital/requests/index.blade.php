@extends('hospital.layouts.hospital')

@section('title', 'طلبات الدم')

@section('content')
    <main id="mainContent" class="main-content">
        <div class="content-wrapper">

            {{-- ========================= إحصائيات ========================= --}}
            <div class="row g-4 mb-4">

                <div class="col-md-4">
                    <div class="stat-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">الطلبات الحرجة</small>
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
                        <input type="text" id="searchBox" class="form-control" placeholder="🔍 البحث عن طلب...">
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
                                    <th>إجراءات</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($requests as $req)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td class="fw-bold">
                                            @if($req->patient_name)
                                                {{ $req->patient_name }}
                                            @else
                                                {{ $req->requester->full_name }}
                                                <small class="text-muted">(مقدم الطلب)</small>
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
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            {{-- ========================= مودال التفاصيل ========================= --}}
            <div class="modal fade" id="viewModal">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content rounded-4 shadow">

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">تفاصيل الطلب</h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info d-none" id="selfPatientNotice">
                                المريض هو نفسه مقدم الطلب.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="fw-bold" id="v_name"></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="fw-bold" id="v_age"></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="fw-bold" id="v_gender"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-bold" id="v_blood"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-bold" id="v_units"></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="fw-bold" id="v_diag"></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="fw-bold" id="v_notes"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ========================= مودال تحديث الحالة ========================= --}}
            <div class="modal fade" id="statusModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <form method="POST" id="statusForm">
                            @csrf

                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title">تحديث الحالة</h5>
                                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" id="status_id">
                                <select id="status_select" class="form-select">
                                    <option value="pending">قيد المراجعة</option>
                                    <option value="approved">مقبول</option>
                                    <option value="in_progress">جاري اكتمال عملية التبرع</option>
                                    <option value="rejected">مرفوض</option>
                                    <option value="completed">مكتمل</option>
                                </select>
                            </div>

                            <div class="modal-footer bg-light">
                                <button class="btn btn-warning text-white w-100">حفظ التحديث</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

            {{-- ========================= مودال إدخال بيانات المريض ========================= --}}
            @include('hospital.requests.patient-modal')


    

            {{-- ====================== مودال إنشاء طلب دم (المستشفى) ====================== --}}
            <div class="modal fade" id="createRequestModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <form id="createRequestForm" action="{{ route('hospital.requests.store') }}" method="POST">
                            @csrf

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-plus me-2"></i>
                                    إنشاء طلب دم
                                </h5>
                                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">

                                    {{-- اسم المريض --}}
                                    <div class="col-md-6">
                                        <label class="form-label">اسم المريض</label>
                                        <input type="text" name="patient_name" class="form-control" required>
                                    </div>

                                    {{-- العمر --}}
                                    <div class="col-md-3">
                                        <label class="form-label">العمر</label>
                                        <input type="number" name="patient_age" class="form-control" min="1" required>
                                    </div>

                                    {{-- الجنس --}}
                                    <div class="col-md-3">
                                        <label class="form-label">الجنس</label>
                                        <select name="patient_gender" class="form-select" required>
                                            <option value="M">ذكر</option>
                                            <option value="F">أنثى</option>
                                        </select>
                                    </div>

                                    {{-- فصيلة الدم --}}
                                    <div class="col-md-3">
                                        <label class="form-label">فصيلة الدم</label>
                                        <select name="blood_type" class="form-select" required>
                                            <option>O+</option>
                                            <option>O-</option>
                                            <option>A+</option>
                                            <option>A-</option>
                                            <option>B+</option>
                                            <option>B-</option>
                                            <option>AB+</option>
                                            <option>AB-</option>
                                        </select>
                                    </div>

                                    {{-- الوحدات --}}
                                    <div class="col-md-3">
                                        <label class="form-label">عدد الوحدات</label>
                                        <input type="number" name="units_requested" class="form-control" min="1" required>
                                    </div>

                                    {{-- الأولوية --}}
                                    <div class="col-md-3">
                                        <label class="form-label">الأولوية</label>
                                        <select name="priority" class="form-select" required>
                                            <option value="normal">عادي</option>
                                            <option value="urgent">عاجل</option>
                                            <option value="critical">حرج</option>
                                        </select>
                                    </div>

                                    {{-- التشخيص --}}
                                    <div class="col-md-9">
                                        <label class="form-label">التشخيص</label>
                                        <input type="text" name="diagnosis" class="form-control">
                                    </div>

                                    {{-- ملاحظات --}}
                                    <div class="col-md-12">
                                        <label class="form-label">ملاحظات إضافية</label>
                                        <textarea name="notes" class="form-control" rows="3"></textarea>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    إلغاء
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    حفظ الطلب
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>



        </div>
    </main>
@endsection

@push('scripts')
    <script>
        function openCreateModal() {
            const modalEl = document.getElementById('createRequestModal');

            if (!modalEl) {
                console.error('createRequestModal not found in DOM');
                return;
            }

            new bootstrap.Modal(modalEl).show();
        }

        /* ========================= البحث ========================= */
        document.getElementById("searchBox").addEventListener("keyup", function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll("#requestsTable tbody tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? "" : "none";
            });
        });

        /* ========================= عرض التفاصيل ========================= */
        function viewRequest(id) {
            fetch(`/hospital/requests/show/${id}`)
                .then(res => res.json())
                .then(req => {
                    const isSelf = !req.patient_name;

                    document.getElementById("v_name").innerText =
                        req.patient_name ?? req.requester.full_name;

                    document.getElementById("v_age").innerText =
                        req.patient_age ?? req.requester.age ?? "—";

                    document.getElementById("v_gender").innerText =
                        req.patient_gender ?? req.requester.gender ?? "—";

                    document.getElementById("v_blood").innerText = req.blood_type;
                    document.getElementById("v_units").innerText = req.units_requested;
                    document.getElementById("v_diag").innerText = req.diagnosis ?? "—";
                    document.getElementById("v_notes").innerText = req.notes ?? "لا توجد ملاحظات";

                    document.getElementById("selfPatientNotice")
                        .classList.toggle("d-none", !isSelf);

                    new bootstrap.Modal(document.getElementById("viewModal")).show();
                });
        }

        /* ========================= تغيير الحالة ========================= */
        function openStatusModal(id, status) {
            document.getElementById("status_id").value = id;
            document.getElementById("status_select").value = status;

            const form = document.getElementById("statusForm");
            form.dataset.requestId = id;

            new bootstrap.Modal(document.getElementById("statusModal")).show();
        }

        /* ========================= أدوات مودال المريض ========================= */
        let currentRequestId = null;

        function togglePatientRequired(enable) {
            document.querySelectorAll("#patientFormFields input, #patientFormFields select")
                .forEach(el => {
                    if (["doctor_name", "diagnosis"].includes(el.name)) {
                        el.removeAttribute("required");
                        return;
                    }

                    enable ? el.setAttribute("required", "required")
                        : el.removeAttribute("required");
                });
        }

        function openPatientModal(req) {
            currentRequestId = req.id;

            document.getElementById("requesterName").innerText = req.requester.full_name;

            document.getElementById("selfPatientBox").classList.remove("d-none");
            document.getElementById("patientFormFields").classList.add("d-none");

            togglePatientRequired(false);

            const form = document.getElementById("patientForm");
            form.action = `/hospital/requests/${req.id}/patient-info`;

            // إزالة أي hidden قديم
            const hidden = form.querySelector('[name="use_requester"]');
            if (hidden) hidden.remove();

            new bootstrap.Modal(document.getElementById("patientModal")).show();
        }

        function showPatientForm() {
            document.getElementById("selfPatientBox").classList.add("d-none");
            document.getElementById("patientFormFields").classList.remove("d-none");

            togglePatientRequired(true);

            const hidden = document.querySelector('[name="use_requester"]');
            if (hidden) hidden.remove();
        }

        function useRequesterAsPatient() {
            togglePatientRequired(false);

            const form = document.getElementById("patientForm");

            if (!form.querySelector('[name="use_requester"]')) {
                form.insertAdjacentHTML(
                    "beforeend",
                    `<input type="hidden" name="use_requester" value="1">`
                );
            }

            form.submit();
        }

        /* ========================= إرسال الحالة ========================= */
        document.getElementById("statusForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const id = this.dataset.requestId;
            const status = document.getElementById("status_select").value;

            fetch(`/hospital/requests/${id}/status`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ status })
            })
                .then(res => res.json())
                .then(data => {
                    bootstrap.Modal.getInstance(
                        document.getElementById("statusModal")
                    ).hide();

                    if (data.request && data.request.status === "approved") {
                        openPatientModal(data.request);
                    } else {
                        location.reload();
                    }
                });
        });
    </script>
@endpush
