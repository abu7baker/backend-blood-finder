@extends('hospital.layouts.hospital')

@section('title', 'مواعيد التبرع')

@section('content')
<main id="mainContent" class="main-content">
    <div class="content-wrapper">

    <div class="card custom-card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold"><i class="fas fa-calendar-check ms-2 text-danger"></i> مواعيد التبرع</h5>

            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="approved">مقبول</option>
                    <option value="cancelled">ملغي</option>
                    <option value="completed">مكتمل</option>
                </select>
            </form>
        </div>

        <div class="card-body">

            <input type="text" class="form-control mb-3" id="searchBox" placeholder="🔍 البحث عن موعد..">

            <div class="table-responsive">
                <table class="table table-hover data-table" id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المتبرع</th>
                            <th>الفصيلة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($appointments as $app)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $app->donor->full_name }}</td>
                            <td><span class="badge bg-danger">{{ $app->donor->blood_type }}</span></td>
                            <td>{{ $app->date_time->format('Y-m-d h:i A') }}</td>

                            <td>
                                @if ($app->status == 'pending')
                                    <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                @elseif($app->status == 'approved')
                                    <span class="badge bg-success">مقبول</span>
                                @elseif($app->status == 'cancelled')
                                    <span class="badge bg-danger">ملغي</span>
                                @else
                                    <span class="badge bg-primary">مكتمل</span>
                                @endif
                            </td>

                            <td>
                                <div class="btn-group btn-group-sm">

                                    <button class="btn btn-outline-primary"
                                        onclick="showAppointment({{ $app->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button class="btn btn-outline-warning"
                                        onclick="editStatus({{ $app->id }}, '{{ $app->status }}')">
                                        <i class="fas fa-sync"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">لا توجد مواعيد حالياً</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>


    <!-- ====================== مودال التفاصيل ====================== -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">تفاصيل الموعد</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="small text-muted">اسم المتبرع</label>
                            <div id="d_name" class="fw-bold"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="small text-muted">رقم الهاتف</label>
                            <div id="d_phone" class="fw-bold"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="small text-muted">الفصيلة</label>
                            <div id="d_blood" class="fw-bold"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="small text-muted">الموعد</label>
                            <div id="d_time" class="fw-bold"></div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- ====================== مودال تعديل الحالة ====================== -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route('hospital.appointments.updateStatus') }}">
                    @csrf

                    <input type="hidden" id="statusId" name="id">

                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">تحديث حالة الموعد</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">اختر الحالة الجديدة</label>
                        <select class="form-select" id="statusSelect" name="status">
                            <option value="pending">قيد الانتظار</option>
                            <option value="approved">مقبول</option>
                            <option value="cancelled">ملغي</option>
                            <option value="completed">مكتمل</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-warning w-100">حفظ</button>
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

    // -------- البحث --------
    document.getElementById("searchBox").addEventListener("keyup", function () {
        let key = this.value.toLowerCase();
        document.querySelectorAll("#appointmentsTable tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(key) ? '' : 'none';
        });
    });


    // -------- عرض التفاصيل --------
    function showAppointment(id) {
        fetch(`/hospital/appointments/${id}/json`)
            .then(res => res.json())
            .then(d => {

                document.getElementById("d_name").innerText  = d.donor_name;
                document.getElementById("d_phone").innerText = d.phone;
                document.getElementById("d_blood").innerText = d.blood_type;
                document.getElementById("d_time").innerText  = d.date_time;

                new bootstrap.Modal(document.getElementById("detailsModal")).show();
            });
    }


    // -------- تعديل الحالة --------
    function editStatus(id, status) {
        document.getElementById("statusId").value = id;
        document.getElementById("statusSelect").value = status;
        new bootstrap.Modal(document.getElementById("statusModal")).show();
    }

</script>
@endpush
