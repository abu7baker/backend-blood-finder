@extends('layouts.admin')

@section('title', 'إدارة المستشفيات')

@section('content')
<main id="mainContent" class="main-content">
 <div class="content-wrapper">
    {{-- الإحصائيات --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">إجمالي المستشفيات</small>
                        <h3 class="mb-0 fw-bold">{{ $total }}</h3>
                    </div>
                    <div class="stat-icon bg-blue">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">نشط</small>
                        <h3 class="mb-0 fw-bold">{{ $active }}</h3>
                    </div>
                    <div class="stat-icon bg-green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">قيد المراجعة</small>
                        <h3 class="mb-0 fw-bold">{{ $pending }}</h3>
                    </div>
                    <div class="stat-icon bg-orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">محظور</small>
                        <h3 class="mb-0 fw-bold">{{ $blocked }}</h3>
                    </div>
                    <div class="stat-icon bg-red">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- الجدول --}}
    <div class="card custom-card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list text-danger ms-2"></i>
                قائمة المستشفيات
            </h5>

            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHospitalModal">
                <i class="fas fa-plus ms-2"></i> إضافة مستشفى
            </button>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table data-table" id="hospitalsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المستشفى</th>
                            <th>المدينة</th>
                            <th>رقم الهاتف</th>
                            <th>البريد</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($hospitals as $hospital)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td><strong>{{ $hospital->name }}</strong></td>

                            <td>{{ $hospital->city }}</td>

                            <td dir="ltr">{{ $hospital->user->phone ?? '-' }}</td>

                            <td>{{ $hospital->user->email ?? '-'}}</td>

                            <td>
                                @if($hospital->status == 'verified')
                                    <span class="status-badge status-active">نشط</span>
                                @elseif($hospital->status == 'pending')
                                    <span class="status-badge status-pending">قيد المراجعة</span>
                                @else
                                    <span class="status-badge status-blocked">محظور</span>
                                @endif
                            </td>

                            <td>{{ $hospital->created_at->format('Y-m-d') }}</td>

                            <td>
                                <div class="btn-group btn-group-sm">

                                    <button class="btn btn-outline-primary"
                                            onclick="viewHospital({{ $hospital->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button class="btn btn-outline-success"
                                            onclick="editHospital({{ $hospital->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="{{ route('admin.hospitals.destroy', $hospital->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>
</main>

{{-- مودال إضافة مستشفى --}}
<div class="modal fade" id="addHospitalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-3">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-hospital text-primary ms-2"></i>
                    إضافة مستشفى جديد
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.hospitals.store') }}" method="POST">
                @csrf

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">اسم المستشفى *</label>
                            <input type="text" name="hospital_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">المدينة *</label>
                            <select name="city" class="form-select" required>
                                <option value="">اختر المدينة</option>
                                <option value="صنعاء">صنعاء</option>
                                <option value="عدن">عدن</option>
                                <option value="تعز">تعز</option>
                                <option value="إب">إب</option>
                                <option value="الحديدة">الحديدة</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">العنوان / الموقع</label>
                            <input type="text" name="location" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">الحالة *</label>
                            <select name="status" class="form-select" required>
                                <option value="verified">نشط</option>
                                <option value="pending">قيد المراجعة</option>
                                <option value="blocked">محظور</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>

                    <button class="btn btn-primary">
                        <i class="fas fa-save ms-2"></i>
                        حفظ
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- مودال عرض المستشفى --}}
<div class="modal fade" id="viewHospitalModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">

            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fas fa-hospital"></i>
                    تفاصيل المستشفى
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">اسم المستشفى</span>
                            <div id="viewHospitalName" class="fw-bold fs-5 mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">المدينة</span>
                            <div id="viewHospitalCity" class="fw-bold fs-5 mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">البريد الإلكتروني</span>
                            <div id="viewHospitalEmail" class="fw-bold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">رقم الهاتف</span>
                            <div id="viewHospitalPhone" class="fw-bold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">العنوان</span>
                            <div id="viewHospitalLocation" class="fw-bold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">الحالة</span>
                            <div id="viewHospitalStatus" class="fw-bold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3 rounded-3 shadow-sm">
                            <span class="text-muted small">تاريخ التسجيل</span>
                            <div id="viewHospitalCreated" class="fw-bold mt-1"></div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer bg-light rounded-bottom-4">
                <button class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> إغلاق
                </button>
            </div>

        </div>
    </div>
</div>

{{-- مودال تعديل المستشفى --}}
<div class="modal fade" id="editHospitalModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">

            <div class="modal-header bg-warning text-white rounded-top-4">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fas fa-edit"></i>
                    تعديل بيانات المستشفى
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="editHospitalForm" method="POST">
                @csrf @method('PUT')

                <input type="hidden" id="edit_id" name="id">

                <div class="modal-body p-4">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="small fw-bold">اسم المستشفى</label>
                            <input type="text" id="edit_hospital_name" name="hospital_name"
                                   class="form-control rounded-3 shadow-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">المدينة</label>
                            <select id="edit_city" name="city" class="form-select rounded-3 shadow-sm">
                                <option value="صنعاء">صنعاء</option>
                                <option value="عدن">عدن</option>
                                <option value="تعز">تعز</option>
                                <option value="الحديدة">الحديدة</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">البريد الإلكتروني</label>
                            <input type="email" id="edit_email" name="email"
                                   class="form-control rounded-3 shadow-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">رقم الهاتف</label>
                            <input type="number" id="edit_phone" name="phone"
                                   class="form-control rounded-3 shadow-sm">
                        </div>

                        <div class="col-md-12">
                            <label class="small fw-bold">العنوان</label>
                            <input type="text" id="edit_location" name="location"
                                   class="form-control rounded-3 shadow-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">الحالة</label>
                            <select id="edit_status" name="status" class="form-select rounded-3 shadow-sm">
                                <option value="verified">نشط</option>
                                <option value="pending">قيد المراجعة</option>
                                <option value="blocked">محظور</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>

                    <button class="btn btn-warning px-4 text-white">
                        <i class="fas fa-save"></i> تحديث البيانات
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function viewHospital(id) {
        fetch(`/admin/hospitals/${id}/json`)
            .then(res => {
                if (!res.ok) throw new Error("خطأ في الاتصال");
                return res.json();
            })
            .then(h => {
                document.getElementById('viewHospitalName').innerText = h.name;
                document.getElementById('viewHospitalCity').innerText = h.city;
                document.getElementById('viewHospitalEmail').innerText = h.user.email;
                document.getElementById('viewHospitalPhone').innerText = h.user.phone;
                document.getElementById('viewHospitalLocation').innerText = h.location ?? '—';
                document.getElementById('viewHospitalStatus').innerText =
                    h.status == "verified" ? 'نشط' :
                    h.status == "pending" ? 'قيد المراجعة' : 'محظور';

                document.getElementById('viewHospitalCreated').innerText = h.created_at;

                new bootstrap.Modal(document.getElementById('viewHospitalModal')).show();
            })
            .catch(err => console.error("JSON Error:", err));
    }

    function editHospital(id) {
        fetch(`/admin/hospitals/${id}/json`)
            .then(res => res.json())
            .then(h => {
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_hospital_name').value = h.name;
                document.getElementById('edit_city').value = h.city;
                document.getElementById('edit_email').value = h.user.email;
                document.getElementById('edit_phone').value = h.user.phone;
                document.getElementById('edit_location').value = h.location ?? '';
                document.getElementById('edit_status').value = h.status;

                document.getElementById('editHospitalForm').action = `/admin/hospitals/${id}`;

                new bootstrap.Modal(document.getElementById('editHospitalModal')).show();
            })
            .catch(err => console.error("خطأ JSON:", err));
    }
</script>
@endpush
