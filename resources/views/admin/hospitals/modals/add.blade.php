<div class="modal fade" id="addHospitalModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form action="{{ route('admin.hospitals.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">إضافة مستشفى جديد</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>اسم المستشفى</label>
                            <input type="text" name="hospital_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>المدينة</label>
                            <select name="city" class="form-select">
                                <option value="صنعاء">صنعاء</option>
                                <option value="عدن">عدن</option>
                                <option value="تعز">تعز</option>
                                <option value="الحديدة">الحديدة</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>رقم الهاتف</label>
                            <input type="number" name="phone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>العنوان</label>
                            <input type="text" name="location" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>الحالة</label>
                            <select name="status" class="form-select">
                                <option value="verified">نشط</option>
                                <option value="pending">قيد المراجعة</option>
                                <option value="blocked">محظور</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button class="btn btn-primary">حفظ</button>
                </div>

            </form>

        </div>
    </div>
</div>
