@extends('hospital.layouts.hospital')

@section('title', 'ÿ·»«  «·œ„')

@section('content')
    <main id="mainContent" class="main-content">
        <div class="content-wrapper">

            @livewire('hospital.requests-table')

            <div class="modal fade" id="viewModal">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content rounded-4 shadow">

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"> ›«’Ì· «·ÿ·»</h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info d-none" id="selfPatientNotice">
                                «·„—Ì÷ ÂÊ ‰›”Â „ﬁœ„ «·ÿ·».
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

            {{-- ========================= „Êœ«·  ÕœÌÀ «·Õ«·… ========================= --}}
            <div class="modal fade" id="statusModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <form method="POST" id="statusForm">
                            @csrf

                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title"> ÕœÌÀ «·Õ«·…</h5>
                                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" id="status_id">
                                <select id="status_select" class="form-select">
                                    <option value="pending">ﬁÌœ «·„—«Ã⁄…</option>
                                    <option value="approved">„ﬁ»Ê·</option>
                                    <option value="in_progress">Ã«—Ì «ﬂ „«· ⁄„·Ì… «· »—⁄</option>
                                    <option value="rejected">„—›Ê÷</option>
                                    <option value="completed">„ﬂ „·</option>
                                </select>
                            </div>

                            <div class="modal-footer bg-light">
                                <button class="btn btn-warning text-white w-100">Õ›Ÿ «· ÕœÌÀ</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

            {{-- ========================= „Êœ«· ≈œŒ«· »Ì«‰«  «·„—Ì÷ ========================= --}}
            @include('hospital.requests.patient-modal')


    

            {{-- ====================== „Êœ«· ≈‰‘«¡ ÿ·» œ„ («·„” ‘›Ï) ====================== --}}
            <div class="modal fade" id="createRequestModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <form id="createRequestForm" action="{{ route('hospital.requests.store') }}" method="POST">
                            @csrf

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-plus me-2"></i>
                                    ≈‰‘«¡ ÿ·» œ„
                                </h5>
                                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">

                                    {{-- «”„ «·„—Ì÷ --}}
                                    <div class="col-md-6">
                                        <label class="form-label">«”„ «·„—Ì÷</label>
                                        <input type="text" name="patient_name" class="form-control" required>
                                    </div>

                                    {{-- «·⁄„— --}}
                                    <div class="col-md-3">
                                        <label class="form-label">«·⁄„—</label>
                                        <input type="number" name="patient_age" class="form-control" min="1" required>
                                    </div>

                                    {{-- «·Ã‰” --}}
                                    <div class="col-md-3">
                                        <label class="form-label">«·Ã‰”</label>
                                        <select name="patient_gender" class="form-select" required>
                                            <option value="M">–ﬂ—</option>
                                            <option value="F">√‰ÀÏ</option>
                                        </select>
                                    </div>

                                    {{-- ›’Ì·… «·œ„ --}}
                                    <div class="col-md-3">
                                        <label class="form-label">›’Ì·… «·œ„</label>
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

                                    {{-- «·ÊÕœ«  --}}
                                    <div class="col-md-3">
                                        <label class="form-label">⁄œœ «·ÊÕœ« </label>
                                        <input type="number" name="units_requested" class="form-control" min="1" required>
                                    </div>

                                    {{-- «·√Ê·ÊÌ… --}}
                                    <div class="col-md-3">
                                        <label class="form-label">«·√Ê·ÊÌ…</label>
                                        <select name="priority" class="form-select" required>
                                            <option value="normal">⁄«œÌ</option>
                                            <option value="urgent">⁄«Ã·</option>
                                            <option value="critical">Õ—Ã</option>
                                        </select>
                                    </div>

                                    {{-- «· ‘ŒÌ’ --}}
                                    <div class="col-md-9">
                                        <label class="form-label">«· ‘ŒÌ’</label>
                                        <input type="text" name="diagnosis" class="form-control">
                                    </div>

                                    {{-- „·«ÕŸ«  --}}
                                    <div class="col-md-12">
                                        <label class="form-label">„·«ÕŸ«  ≈÷«›Ì…</label>
                                        <textarea name="notes" class="form-control" rows="3"></textarea>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    ≈·€«¡
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Õ›Ÿ «·ÿ·»
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

        /* ========================= «·»ÕÀ ========================= */
function viewRequest(id) {
            fetch(`/hospital/requests/show/${id}`)
                .then(res => res.json())
                .then(req => {
                    const isSelf = !req.patient_name;

                    document.getElementById("v_name").innerText =
                        req.patient_name ?? req.requester.full_name;

                    document.getElementById("v_age").innerText =
                        req.patient_age ?? req.requester.age ?? "ó";

                    document.getElementById("v_gender").innerText =
                        req.patient_gender ?? req.requester.gender ?? "ó";

                    document.getElementById("v_blood").innerText = req.blood_type;
                    document.getElementById("v_units").innerText = req.units_requested;
                    document.getElementById("v_diag").innerText = req.diagnosis ?? "ó";
                    document.getElementById("v_notes").innerText = req.notes ?? "·«  ÊÃœ „·«ÕŸ« ";

                    document.getElementById("selfPatientNotice")
                        .classList.toggle("d-none", !isSelf);

                    new bootstrap.Modal(document.getElementById("viewModal")).show();
                });
        }

        /* =========================  €ÌÌ— «·Õ«·… ========================= */
        function openStatusModal(id, status) {
            document.getElementById("status_id").value = id;
            document.getElementById("status_select").value = status;

            const form = document.getElementById("statusForm");
            form.dataset.requestId = id;

            new bootstrap.Modal(document.getElementById("statusModal")).show();
        }

        /* ========================= √œÊ«  „Êœ«· «·„—Ì÷ ========================= */
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

            // ≈“«·… √Ì hidden ﬁœÌ„
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

        /* ========================= ≈—”«· «·Õ«·… ========================= */
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


