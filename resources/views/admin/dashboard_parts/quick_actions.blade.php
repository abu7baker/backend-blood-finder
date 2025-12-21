<div class="card custom-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-bolt text-danger ms-2"></i>
            الإجراءات السريعة
        </h5>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.users.index') }}" class="action-card action-card-blue">
                    <div class="action-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">إدارة المستخدمين</h6>
                        <small class="text-white-75">عرض وإدارة حسابات المستخدمين</small>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.hospitals.index') }}" class="action-card action-card-green">
                    <div class="action-icon"><i class="fas fa-hospital"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">إدارة المستشفيات</h6>
                        <small class="text-white-75">الموافقة على المستشفيات</small>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.users.index') }}" class="action-card action-card-red">
                    <div class="action-icon"><i class="fas fa-droplet"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">طلبات الدم</h6>
                        <small class="text-white-75">إدارة طلبات وحدات الدم</small>
                    </div>
                </a>
            </div>

                        <div class="col-md-6 col-lg-4">
                            <a href="reports.html" class="action-card action-card-purple">
                                <div class="action-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="action-content">
                                    <h6 class="fw-bold mb-1">التقارير والإحصائيات</h6>
                                    <small class="text-white-75">عرض تقارير شاملة وإحصائيات مفصلة</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="security.html" class="action-card action-card-orange">
                                <div class="action-icon">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                                <div class="action-content">
                                    <h6 class="fw-bold mb-1">الأمان والصلاحيات</h6>
                                    <small class="text-white-75">إدارة أذونات المستخدمين والأمان</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="settings.html" class="action-card action-card-gray">
                                <div class="action-icon">
                                    <i class="fas fa-gear"></i>
                                </div>
                                <div class="action-content">
                                    <h6 class="fw-bold mb-1">إعدادات النظام</h6>
                                    <small class="text-white-75">تكوين الإعدادات العامة للتطبيق</small>
                                </div>
                            </a>
                        </div>
        </div>
    </div>
</div>
