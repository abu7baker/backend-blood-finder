    <div class="modal fade" id="patientModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">

                    <form method="POST" id="patientForm">
                        @csrf

                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">تأكيد / إدخال بيانات المريض</h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            {{-- حالة: المريض هو مقدم الطلب --}}
                            <div class="alert alert-info" id="selfPatientBox">
                                <strong>تنبيه:</strong>
                                هل المريض هو نفسه مقدم الطلب؟
                                <br>
                                <span class="fw-bold" id="requesterName"></span>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="useRequesterAsPatient()">
                                        نعم، المريض هو مقدم الطلب
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="showPatientForm()">
                                        لا، المريض شخص آخر
                                    </button>
                                </div>
                            </div>

                            {{-- فورم إدخال بيانات مريض آخر --}}
                            <div id="patientFormFields" class="d-none">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="form-label">اسم المريض</label>
                                        <input type="text" name="patient_name" class="form-control" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">العمر</label>
                                        <input type="number" name="patient_age" class="form-control" min="0" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">الجنس</label>
                                        <select name="patient_gender" class="form-select" required>
                                            <option value="">اختر</option>
                                            <option value="male">ذكر</option>
                                            <option value="female">أنثى</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">اسم الطبيب (اختياري)</label>
                                        <input type="text" name="doctor_name" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">التشخيص (اختياري)</label>
                                        <input type="text" name="diagnosis" class="form-control">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="modal-footer bg-light">
                            <button class="btn btn-info text-white w-100">
                                حفظ بيانات المريض
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>


    