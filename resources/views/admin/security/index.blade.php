@extends('layouts.admin')

@section('title', 'الأمان والصلاحيات')

@section('content')

    <main id="mainContent" class="main-content">
        <div class="content-wrapper">

            {{-- ================= Security Stats ================= --}}
            <div class="row g-4 mb-4">

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">المسؤولين النشطين</small>
                                <h3 class="mb-0 fw-bold">{{ $activeAdmins }}</h3>
                            </div>
                            <div class="stat-icon bg-blue"><i class="fas fa-user-shield"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">محاولات الدخول اليوم</small>
                                <h3 class="mb-0 fw-bold">{{ $todayLogins ?? 0 }}</h3>
                            </div>
                            <div class="stat-icon bg-green"><i class="fas fa-right-to-bracket"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">محاولات فاشلة</small>
                                <h3 class="mb-0 fw-bold">{{ $failedLogins ?? 0 }}</h3>
                            </div>
                            <div class="stat-icon bg-red"><i class="fas fa-ban"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">الصلاحيات النشطة</small>
                                <h3 class="mb-0 fw-bold">{{ $rolesCount }}</h3>
                            </div>
                            <div class="stat-icon bg-purple"><i class="fas fa-key"></i></div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ================= Tabs ================= --}}
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#roles">
                        <i class="fas fa-user-tag ms-2"></i>الأدوار
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#permissions">
                        <i class="fas fa-key ms-2"></i>الصلاحيات
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity">
                        <i class="fas fa-clock ms-2"></i>سجل النشاطات
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sessions">
                        <i class="fas fa-users ms-2"></i>الجلسات النشطة
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                {{-- ================= Roles ================= --}}
                <div class="tab-pane fade show active" id="roles">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tag text-danger ms-2"></i>
                                الأدوار
                            </h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="fas fa-plus ms-2"></i>إضافة دور
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="row g-4">

                                @foreach($roles as $role)

                                    @php
                                        $icon = match ($role->name) {
                                            'admin' => 'fa-crown',
                                            'hospital' => 'fa-hospital',
                                            default => 'fa-user'
                                        };

                                        $color = match ($role->name) {
                                            'admin' => 'danger',
                                            'hospital' => 'primary',
                                            default => 'success'
                                        };

                                        // وصف ثابت في حال لم يوجد
                                        $description = $role->description
                                            ?? 'دور افتراضي في النظام';
                                    @endphp

                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border h-100">
                                            <div class="card-body">

                                                {{-- Header --}}
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h5 class="mb-1">
                                                            <i class="fas {{ $icon }} text-{{ $color }} ms-2"></i>
                                                            {{ ucfirst($role->name) }}
                                                        </h5>
                                                        <small class="text-muted">
                                                            {{ $description }}
                                                        </small>
                                                    </div>

                                                    <span class="badge bg-{{ $color }}">
                                                        {{ $role->users_count }}
                                                    </span>
                                                </div>

                                                {{-- صلاحيات (Static Display) --}}
                                                <div class="mb-3">
                                                    <small class="text-muted d-block mb-1">الصلاحيات:</small>
                                                    <div class="d-flex flex-wrap gap-1">

                                                        @if($role->name === 'admin')
                                                            <span class="badge bg-success">قراءة</span>
                                                            <span class="badge bg-success">كتابة</span>
                                                            <span class="badge bg-success">تعديل</span>
                                                            <span class="badge bg-success">حذف</span>

                                                        @elseif($role->name === 'hospital')
                                                            <span class="badge bg-success">قراءة</span>
                                                            <span class="badge bg-success">كتابة</span>
                                                            <span class="badge bg-success">تعديل</span>

                                                        @else
                                                            <span class="badge bg-success">قراءة</span>
                                                            <span class="badge bg-success">كتابة</span>
                                                        @endif

                                                    </div>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="d-flex gap-2">

                                                    {{-- تعديل --}}
                                                    <button class="btn btn-sm btn-outline-primary flex-fill"
                                                        data-bs-toggle="modal" data-bs-target="#editRoleModal-{{ $role->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    {{-- حذف --}}
                                                    <button class="btn btn-sm btn-outline-danger flex-fill"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteRoleModal-{{ $role->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                </div>

                                            </div>
                                        </div>
                                    </div> {{-- ================= Edit Role Modal ================= --}}
                                    <div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">

                                        <div class="modal-dialog modal-dialog-centered">
                                            <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-edit text-primary ms-2"></i>
                                                            تعديل الدور
                                                        </h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">اسم الدور</label>
                                                            <input type="text" name="name" class="form-control"
                                                                value="{{ $role->name }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">الوصف</label>
                                                            <textarea name="description" class="form-control"
                                                                rows="3">{{ $role->description }}</textarea>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            إلغاء
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">
                                                            حفظ التعديلات
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>



                                    {{-- ================= Delete Role Modal ================= --}}
                                    <div class="modal fade" id="deleteRoleModal-{{ $role->id }}" tabindex="-1"
                                        aria-hidden="true">

                                        <div class="modal-dialog modal-dialog-centered">
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}">
                                                @csrf
                                                @method('DELETE')

                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-danger">
                                                            <i class="fas fa-trash ms-2"></i>
                                                            حذف الدور
                                                        </h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body text-center">
                                                        <p class="mb-0">
                                                            هل أنت متأكد من حذف الدور:
                                                            <strong>{{ $role->name }}</strong>؟
                                                        </p>
                                                    </div>

                                                    <div class="modal-footer justify-content-center">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            إلغاء
                                                        </button>
                                                        <button type="submit" class="btn btn-danger">
                                                            نعم، حذف
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>


                {{-- ================= Permissions ================= --}}
                <div class="tab-pane fade" id="permissions">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-key text-danger ms-2"></i>
                                الصلاحيات المتاحة (RBAC)
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table data-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>الصلاحية</th>
                                            <th>الوصف</th>
                                            <th>Admin</th>
                                            <th>Hospital</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td><strong>إدارة المستخدمين</strong></td>
                                            <td>إضافة، تعديل، حظر المستخدمين</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>إدارة المستشفيات</strong></td>
                                            <td>اعتماد وتفعيل حسابات المستشفيات</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>طلبات الدم</strong></td>
                                            <td>إنشاء ومعالجة طلبات الدم</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>إدارة التبرعات</strong></td>
                                            <td>تسجيل ومتابعة التبرعات</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>إدارة مخزون الدم</strong></td>
                                            <td>تحديث وحدات وفصائل الدم</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>التقارير والإحصائيات</strong></td>
                                            <td>عرض وتحليل بيانات النظام</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>إعدادات النظام</strong></td>
                                            <td>التحكم بالإعدادات العامة والأمان</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>

                                        <tr>
                                            <td><strong>الجلسات وسجل النشاطات</strong></td>
                                            <td>مراقبة الجلسات وتسجيل الأحداث</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-muted small">
                                <i class="fas fa-shield-halved ms-1"></i>
                                يعتمد النظام على التحكم بالوصول المعتمد على الأدوار (RBAC) بصلاحيات ثابتة غير قابلة للتعديل.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= Activity ================= --}}
                <div class="tab-pane fade" id="activity">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock text-danger ms-2"></i>
                                سجل النشاطات
                            </h5>
                        </div>

                        <div class="card-body">

                            {{-- بحث (شكلي حالياً) --}}
                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="🔍 البحث في سجل النشاطات...">
                            </div>

                            <div class="table-responsive">
                                <table class="table data-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>التاريخ والوقت</th>
                                            <th>المستخدم</th>
                                            <th>النشاط</th>
                                            <th>التفاصيل</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($activityLogs as $log)
                                            <tr>
                                                {{-- التاريخ --}}
                                                <td>
                                                    {{ $log->created_at->format('Y-m-d H:i') }}
                                                </td>

                                                {{-- المستخدم --}}
                                                <td>
                                                    <strong>
                                                        {{ $log->user?->full_name ?? 'زائر' }}
                                                    </strong>
                                                </td>

                                                {{-- النشاط --}}
                                                <td>
                                                    <span class="badge bg-{{ $log->color }}">
                                                        {{ $log->label }}
                                                    </span>
                                                </td>

                                                {{-- التفاصيل --}}
                                                <td>
                                                    {!! $log->description ?? '-' !!}
                                                </td>

                                                {{-- IP --}}
                                                <td dir="ltr">
                                                    {{ $log->ip_address ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    لا توجد نشاطات مسجلة حتى الآن
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>



                {{-- ================= Active Sessions ================= --}}
                <div class="tab-pane fade" id="sessions">
                    <div class="card custom-card">
                        <div class="card-body table-responsive">
                            <table class="table data-table align-middle">
                                <thead>
                                    <tr>
                                        <th>المستخدم</th>
                                        <th>الدور</th>
                                        <th>وقت الدخول</th>
                                        <th>آخر نشاط</th>
                                        <th>IP</th>
                                        <th>الجهاز</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse($activeSessions as $session)
                                        <tr>
                                            <td class="fw-bold">{{ $session->full_name ?? 'Guest' }}</td>

                                            <td>
                                                @php
                                                    $roleColor = match ($session->role_name) {
                                                        'admin' => 'danger',
                                                        'hospital' => 'primary',
                                                        default => 'success'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $roleColor }}">
                                                    {{ $session->role_name ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- وقت الدخول --}}
                                            <td>
                                                {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('H:i') }}
                                            </td>

                                            {{-- آخر نشاط --}}
                                            <td>
                                                {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                                            </td>

                                            <td dir="ltr">{{ $session->ip_address ?? '-' }}</td>

                                            <td>
                                                @php
                                                    $userAgent = $session->user_agent ?? '';
                                                    $deviceName = $session->device_name ?? '';
                                                @endphp
                                                @if($session->session_type === 'token')
                                                    <i class="fas fa-mobile"></i> {{ $deviceName !== '' ? $deviceName : 'تطبيق' }}
                                                @elseif(
                                                    str_contains($userAgent, 'Mobile') ||
                                                    str_contains($userAgent, 'Android') ||
                                                    str_contains($userAgent, 'iPhone')
                                                )
                                                    <i class="fas fa-mobile"></i> هاتف
                                                @elseif(
                                                    str_contains($userAgent, 'Tablet') ||
                                                    str_contains($userAgent, 'iPad')
                                                )
                                                    <i class="fas fa-tablet"></i> جهاز لوحي
                                                @else
                                                    <i class="fas fa-desktop"></i> كمبيوتر
                                                @endif
                                            </td>

                                            <td>
                                                <form method="POST"
                                                    action="{{ route('admin.security.sessions.destroy', [$session->session_type, $session->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                لا توجد جلسات نشطة حاليًا
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>





@endsection
