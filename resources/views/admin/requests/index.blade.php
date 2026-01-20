@extends('layouts.admin')

@section('title', '≈œ«—… ÿ·»«  «·œ„')

@section('content')
<main id="mainContent" class="main-content">
    <div class="content-wrapper">

        @livewire('admin.blood-requests-table')

        <div class="modal fade" id="viewRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle me-2"></i>
                             ›«’Ì· «·ÿ·»
                        </h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="text-muted small">‰Ê⁄ «·ÿ·»</label>
                                <div id="viewType" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">«·Õ«·…</label>
                                <div id="viewStatus" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">«·„” ‘›Ï</label>
                                <div id="viewHospital" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">«·›’Ì·… / «·ÊÕœ« </label>
                                <div id="viewBlood" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">«”„ «·„—Ì÷</label>
                                <div id="viewPatientName" class="fw-bold"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="text-muted small">«·⁄„—</label>
                                <div id="viewPatientAge" class="fw-bold"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="text-muted small">«·Ã‰”</label>
                                <div id="viewPatientGender" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">«”„ «·ÿ»Ì»</label>
                                <div id="viewDoctor" class="fw-bold"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">«· ‘ŒÌ’</label>
                                <div id="viewDiag" class="fw-bold"></div>
                            </div>

                            <div class="col-12">
                                <label class="text-muted small">„·«ÕŸ« </label>
                                <div id="viewNotes" class="fw-bold"></div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">≈€·«ﬁ</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ====================== „Êœ«·  ⁄œÌ· «·ÿ·» ====================== --}}
        <div class="modal fade" id="editRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <form id="editRequestForm" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i>
                                 ⁄œÌ· «·ÿ·»
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">«·ÊÕœ«  «·„ÿ·Ê»…</label>
                                    <input type="number" name="units_requested" id="editUnits" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">«·√Ê·ÊÌ…</label>
                                    <select name="priority" id="editPriority" class="form-select">
                                        <option value="normal">⁄«œÌ</option>
                                        <option value="urgent">⁄«Ã·</option>
                                        <option value="critical">Õ—Ã</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">„·«ÕŸ« </label>
                                    <textarea name="notes" id="editNotes" class="form-control"></textarea>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="submit" class="btn btn-success">Õ›Ÿ «· ⁄œÌ·« </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== „Êœ«·  €ÌÌ— «·Õ«·… ====================== --}}
        <div class="modal fade" id="editStatusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="editStatusForm" method="POST">
                        @csrf

                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-sync me-2"></i>
                                 ÕœÌÀ «·Õ«·…
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <label class="form-label">«Œ — «·Õ«·… «·ÃœÌœ…</label>
                            <select class="form-select" name="status" id="editStatusSelect" required>
                                <option value="pending">ﬁÌœ «·„—«Ã⁄…</option>
                                <option value="approved">„ﬁ»Ê·</option>
                                <option value="in_progress">Ã«—Ì «ﬂ „«· ⁄„·Ì… «· »—⁄</option>
                                <option value="rejected">„—›Ê÷</option>
                                <option value="completed">„ﬂ „·</option>
                            </select>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="submit" class="btn btn-warning text-white">Õ›Ÿ</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== „Êœ«· ”Ã· «·Õ«·«  ====================== --}}
        <div class="modal fade" id="historyModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-history me-2"></i>
                            ”Ã· «· €ÌÌ—« 
                        </h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div id="historyBody"> Õ„Ì·...</div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">≈€·«ﬁ</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ====================== „Êœ«· «·Õ–› ====================== --}}
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-trash me-2"></i>
                                Õ–› «·ÿ·»
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>Â· √‰  „ √ﬂœ √‰ﬂ  —Ìœ Õ–› Â–« «·ÿ·»ø</p>
                        </div>

                        <div class="modal-footer bg-light">
                            <button class="btn btn-danger">Õ–› «·¬‰</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== „Êœ«· ≈‰‘«¡ ÿ·» ÃœÌœ ====================== --}}
        <div class="modal fade" id="createRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <form id="createRequestForm" action="{{ route('admin.requests.store') }}" method="POST">
                        @csrf

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-plus me-2"></i>
                                ≈÷«›… ÿ·» œ„ ÃœÌœ
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">«·„—Ì÷</label>
                                    <input type="text" name="patient_name" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">«·⁄„—</label>
                                    <input type="number" name="patient_age" class="form-control" min="1" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">«·Ã‰”</label>
                                    <select name="patient_gender" class="form-select" required>
                                        <option value="M">–ﬂ—</option>
                                        <option value="F">√‰ÀÏ</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">«·„” ‘›Ï</label>
                                    <select name="hospital_id" class="form-select" required>
                                        @foreach($hospitals as $h)
                                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">«·›’Ì·…</label>
                                    <select name="blood_type" class="form-select" required>
                                        <option>O+</option><option>O-</option>
                                        <option>A+</option><option>A-</option>
                                        <option>B+</option><option>B-</option>
                                        <option>AB+</option><option>AB-</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">«·ÊÕœ« </label>
                                    <input type="number" name="units_requested" class="form-control" min="1" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">«·√Ê·ÊÌ…</label>
                                    <select name="priority" class="form-select" required>
                                        <option value="normal">⁄«œÌ</option>
                                        <option value="urgent">⁄«Ã·</option>
                                        <option value="critical">Õ—Ã</option>
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">«· ‘ŒÌ’</label>
                                    <input type="text" name="diagnosis" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">„·«ÕŸ«  ≈÷«›Ì…</label>
                                    <textarea name="notes" class="form-control"></textarea>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">≈·€«¡</button>
                            <button type="submit" class="btn btn-primary">Õ›Ÿ</button>
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
    // »ÕÀ »”Ìÿ œ«Œ· «·ÃœÊ·
function openCreateModal() {
        new bootstrap.Modal(document.getElementById('createRequestModal')).show();
    }

    // ⁄—÷  ›«’Ì· «·ÿ·»
    function viewRequest(id) {
        fetch(`/admin/blood-requests/${id}/json`)
            .then(res => res.json())
            .then(req => {
                const isHospital = req.requester && req.requester.role === 'hospital';

                document.getElementById('viewType').innerText =
                    isHospital ? "ÿ·» „‰ „” ‘›Ï" : "ÿ·» „‰ „” Œœ„";

                document.getElementById('viewStatus').innerText    = req.status ?? 'ó';
                document.getElementById('viewHospital').innerText  = (req.hospital && req.hospital.name) ? req.hospital.name : 'ó';
                document.getElementById('viewBlood').innerText     = `${req.blood_type ?? ''} / ${req.units_requested ?? 0}`;
                document.getElementById('viewPatientName').innerText   = req.patient_name ?? 'ó';
                document.getElementById('viewPatientAge').innerText    = req.patient_age ?? 'ó';
                document.getElementById('viewPatientGender').innerText = req.patient_gender ?? 'ó';
                document.getElementById('viewDoctor').innerText        = req.doctor_name ?? 'ó';
                document.getElementById('viewDiag').innerText          = req.diagnosis ?? 'ó';
                document.getElementById('viewNotes').innerText         = req.notes ?? 'ó';

                new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
            })
            .catch(() => alert('ÕœÀ Œÿ√ √À‰«¡ Ã·» »Ì«‰«  «·ÿ·»'));
    }

    // › Õ „Êœ«·  ⁄œÌ· «·ÿ·» „⁄  ⁄»∆… «·»Ì«‰« 
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
            .catch(() => alert('ÕœÀ Œÿ√ √À‰«¡ Ã·» »Ì«‰«  «·ÿ·»'));
    }

    // › Õ „Êœ«·  ⁄œÌ· «·Õ«·…
    function editStatus(id, currentStatus) {
        document.getElementById('editStatusSelect').value = currentStatus;
        document.getElementById('editStatusForm').action  =
            `/admin/blood-requests/${id}/status`;

        new bootstrap.Modal(document.getElementById('editStatusModal')).show();
    }

    // › Õ „Êœ«· «·Õ–›
    function deleteRequest(id) {
        document.getElementById('deleteForm').action =
            `/admin/blood-requests/${id}`;

        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    //  Õ„Ì· ”Ã· «·Õ«·«  Ê⁄—÷Â ›Ì «·„Êœ«·
    function loadHistory(id) {
        fetch(`/admin/blood-requests/${id}/history`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('historyBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('historyModal')).show();
            })
            .catch(() => alert(' ⁄–—  Õ„Ì· ”Ã· «·Õ«·« '));
    }
</script>
@endpush
@endsection


