@extends('layouts.admin')

@section('title', 'إدارة التبرعات')

@section('content')
<main id="mainContent" class="main-content">
    <div class="content-wrapper">

        {{-- ====================== الإحصائيات ====================== --}}
        <div class="row g-4 mb-4">

            {{-- مكتملة --}}
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">تبرعات مكتملة</small>
                            <h3 class="fw-bold text-success">{{ $stats['completed'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- قيد التنفيذ --}}
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">قيد التنفيذ</small>
                            <h3 class="fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ملغية --}}
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">ملغية</small>
                            <h3 class="fw-bold text-danger">{{ $stats['canceled'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ====================== الجدول ====================== --}}
        <div class="card custom-card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="fas fa-hand-holding-medical text-danger ms-2"></i>
                    قائمة التبرعات
                </h5>

                {{-- فلاتر --}}
                <form method="GET" class="d-flex gap-2">

                    {{-- حسب الحالة --}}
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">كل الحالات</option>
                        <option value="willing">موافقة</option>
                        <option value="pending">قيد التنفيذ</option>
                        <option value="completed">مكتملة</option>
                        <option value="canceled">ملغية</option>
                    </select>

                    {{-- حسب الفصيلة --}}
                    <select name="blood" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">كل الفصائل</option>
                        @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>

                </form>
            </div>

            <div class="card-body">

                {{-- البحث --}}
                <div class="mb-3">
                    <input type="text" id="searchDonations" class="form-control" placeholder="🔍 البحث...">
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="donationsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المتبرع</th>
                                <th>الفصيلة</th>
                                <th>المستشفى</th>
                                <th>نوع التبرع</th>
                                <th>الحالة</th>
                                <th>تاريخ التسجيل</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($donations as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    {{-- المتبرع --}}
                                    <td>
                                        <strong>{{ $d->donor->full_name }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-phone text-muted"></i>
                                            {{ $d->donor->phone }}
                                        </small>
                                    </td>

                                    {{-- الفصيلة --}}
                                    <td>
                                        <span class="badge bg-danger">{{ $d->blood_type }}</span>
                                    </td>

                                    {{-- المستشفى --}}
                                    <td>{{ $d->hospital->name ?? '—' }}</td>

                              
                                                {{-- نوع التبرع --}}
                                    <td>
                                        @if(($d->source === 'blood_request') || $d->request_id)
                                            <a class="badge bg-primary text-decoration-none"
                                               href="{{ route('admin.requests.index', ['open_request' => $d->request_id]) }}">
                                                عبر موافقة على طلب دم (#{{ $d->request_id }})
                                            </a>
                                        @elseif($d->source === 'appointment')
                                            <span class="badge bg-info text-dark">
                                                عبر موعد تبرع
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                غير محدد
                                            </span>
                                        @endif
                                    </td>


                                    {{-- الحالة --}}
                                    <td>
                                        @switch($d->status)
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

                                    {{-- تاريخ التسجيل --}}
                                    <td>
                                        @if($d->donated_at)
                                            {{ \Carbon\Carbon::parse($d->donated_at)->format('Y-m-d h:i A') }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- الإجراءات --}}
                                    <td>
                                        <div class="btn-group btn-group-sm">

                                            {{-- تفاصيل --}}
                                            <button class="btn btn-outline-primary"
                                                    onclick="viewDonation({{ $d->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            {{-- تعديل --}}
                                            <button class="btn btn-outline-warning"
                                                    onclick="editDonationStatus({{ $d->id }}, '{{ $d->status }}')">
                                                <i class="fas fa-sync"></i>
                                            </button>

                                            {{-- حذف --}}
                                            <button class="btn btn-outline-danger"
                                                    onclick="deleteDonation({{ $d->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">لا توجد تبرعات.</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>
        </div>


        {{-- ====================== مودال التفاصيل ====================== --}}
        <div class="modal fade" id="viewDonationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle me-2"></i>
                            تفاصيل التبرع
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body" id="donationDetails">
                        تحميل...
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ====================== مودال تعديل الحالة ====================== --}}
        <div class="modal fade" id="editDonationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="editDonationForm" method="POST">
                        @csrf

                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">تحديث حالة التبرع</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <label class="form-label">اختر الحالة</label>
                            <select name="status" id="editStatusSelect" class="form-select">
                                <option value="willing">موافقة</option>
                                <option value="pending">قيد التنفيذ</option>
                                <option value="completed">مكتمل</option>
                                <option value="canceled">ملغى</option>
                            </select>

                            <div class="mt-3" id="unitsBox" style="display:none">
                                <label>الوحدات المتبرع بها</label>
                                <input type="number" name="units_donated" class="form-control">
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-warning text-white">حفظ</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- ====================== مودال الحذف ====================== --}}
        <div class="modal fade" id="deleteDonationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="deleteDonationForm" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">حذف التبرع</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            هل أنت متأكد أنك تريد حذف هذا التبرع؟
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-danger">حذف الآن</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</main>


{{-- ================== JavaScript ================== --}}
@push('scripts')
<script>

    // بحث داخل الجدول
    document.getElementById('searchDonations').addEventListener('keyup', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#donationsTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    // عرض تفاصيل التبرع
    function viewDonation(id) {
        fetch(`/admin/donations/${id}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('donationDetails').innerHTML = html;
                new bootstrap.Modal(document.getElementById('viewDonationModal')).show();
            });
    }

    // تعديل الحالة
    function editDonationStatus(id, current) {
        document.getElementById('editStatusSelect').value = current;

        document.getElementById('unitsBox').style.display =
            current === 'completed' ? '' : 'none';

        document.getElementById('editStatusSelect').addEventListener('change', function () {
            document.getElementById('unitsBox').style.display =
                this.value === 'completed' ? '' : 'none';
        });

        document.getElementById('editDonationForm').action =
            `/admin/donations/${id}/status`;

        new bootstrap.Modal(document.getElementById('editDonationModal')).show();
    }

    // حذف
    function deleteDonation(id) {
        document.getElementById('deleteDonationForm').action =
            `/admin/donations/${id}`;

        new bootstrap.Modal(document.getElementById('deleteDonationModal')).show();
    }

</script>
@endpush

@endsection
