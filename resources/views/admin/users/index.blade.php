@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@section('content')

    <main id="mainContent" class="main-content">


        <div class="content-wrapper">

            {{-- Stats --}}
            <div class="row g-4 mb-4">

                <div class="col-md-3">
                    <div class="stat-card stat-card-blue">
                        <div class="stat-card-body">
                            <div class="stat-info">
                                <small class="text-muted">إجمالي المستخدمين</small>
                                <h3 class="fw-bold">{{ $totalUsers }}</h3>
                            </div>
                            <div class="stat-icon bg-blue">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card stat-card-green">
                        <div class="stat-card-body">
                            <div class="stat-info">
                                <small class="text-muted">نشط</small>
                                <h3 class="fw-bold">{{ $activeUsers }}</h3>
                            </div>
                            <div class="stat-icon bg-green">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card stat-card-red">
                        <div class="stat-card-body">
                            <div class="stat-info">
                                <small class="text-muted">محظور</small>
                                <h3 class="fw-bold">{{ $blockedUsers }}</h3>
                            </div>
                            <div class="stat-icon bg-red">
                                <i class="fas fa-user-slash"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Users Table --}}
            <div class="card custom-card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-danger ms-2"></i>
                        قائمة المستخدمين
                    </h5>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-success" onclick="exportUsersCSV()">
                            <i class="fas fa-file-excel ms-2"></i> تصدير Excel
                        </button>

                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus ms-2"></i> إضافة مستخدم
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    {{-- Search + Filter --}}
                    <div class="search-filter-bar mb-4">
                        <input type="text" id="searchUsers" class="form-control search-input"
                            placeholder="🔍 البحث عن مستخدم...">

                        <select class="form-select" id="filterStatus">
                            <option value="all">جميع الحالات</option>
                            <option value="active">نشط</option>
                            <option value="pending">قيد المراجعة</option>
                            <option value="blocked">محظور</option>
                        </select>

                        <select class="form-select" id="filterType">
                            <option value="all">جميع الأنواع</option>
                            <option value="admin">مدير</option>
                            <option value="user">مستخدم</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover data-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>البريد</th>
                                    <th>الهاتف</th>
                                    <th>الفصيلة</th>
                                    <th>المدينة</th>
                                    <th>النوع</th>
                                    <th>الحالة</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar-small">
                                                    {{ mb_substr($user->full_name, 0, 1) }}
                                                </div>
                                                <strong>{{ $user->full_name }}</strong>
                                            </div>
                                        </td>

                                        <td>{{ $user->email ?? '—' }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td><span class="badge bg-danger">{{ $user->blood_type }}</span></td>

                                        <td>{{ $user->city }}</td>

                                        <td>
                                            @if($user->role->name === 'admin')
                                                <span class="badge bg-dark">مدير</span>
                                            @elseif($user->role->name === 'hospital')
                                                <span class="badge bg-primary">مستشفى</span>
                                            @else
                                                <span class="badge bg-danger">مستخدم</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($user->status === 'active')
                                                <span class="status-badge status-active">نشط</span>
                                            @elseif($user->status === 'pending')
                                                <span class="status-badge status-pending">قيد الانتظار</span>
                                            @else
                                                <span class="status-badge status-blocked">محظور</span>
                                            @endif
                                        </td>

                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>

                                        <td>
                                            <div class="btn-group btn-group-sm">

                                                <button class="btn btn-outline-primary" onclick="viewUser({{ $user->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>



                                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                                    class="btn btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                                    onsubmit="return confirm('حذف المستخدم؟')">
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <form action="{{ route('admin.users.store') }}" method="POST" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">إضافة مستخدم جديد</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label">الاسم الكامل</label>
                    <input name="full_name" class="form-control mb-2" required>

                    <label class="form-label">البريد الإلكتروني</label>
                    <input name="email" class="form-control mb-2">

                    <label class="form-label">رقم الهاتف</label>
                    <input name="phone" class="form-control mb-2" required>

                    <label class="form-label">فصيلة الدم</label>
                    <select name="blood_type" class="form-select mb-2">
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>O+</option>
                        <option>O-</option>
                        <option>AB+</option>
                        <option>AB-</option>
                    </select>

                    <label class="form-label">المدينة</label>
                    <input name="city" class="form-control mb-2">

                    <label class="form-label">النوع</label>
                    <select name="role_id" class="form-select mb-2" required>
                        <option value="1">مدير</option>
                        <option value="2">مستشفى</option>
                        <option value="3">مستخدم</option>
                    </select>

                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select mb-2">
                        <option value="active">نشط</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="blocked">محظور</option>
                    </select>

                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control mb-2" required>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button class="btn btn-primary">حفظ</button>
                </div>

            </form>
        </div>
    </div>


    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user text-primary ms-2"></i>
                        تفاصيل المستخدم
                    </h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-4 text-center">
                            <div id="viewUserAvatar" class="admin-avatar"
                                style="width:100px;height:100px;font-size:2rem;margin:auto">
                                ?
                            </div>

                            <h4 id="viewUserName" class="mt-3 fw-bold"></h4>
                            <span id="viewUserStatus" class="status-badge"></span>
                        </div>

                        <div class="col-md-8">

                            <div class="p-3 rounded bg-light">

                                <p class="mb-2"><small class="text-muted">البريد الإلكتروني:</small><br>
                                    <span id="viewUserEmail"></span>
                                </p>

                                <p class="mb-2"><small class="text-muted">الهاتف:</small><br>
                                    <span id="viewUserPhone"></span>
                                </p>

                                <p class="mb-2"><small class="text-muted">المدينة:</small><br>
                                    <span id="viewUserCity"></span>
                                </p>

                                <p class="mb-2"><small class="text-muted">النوع:</small><br>
                                    <span id="viewUserRole"></span>
                                </p>

                                <p class="mb-2"><small class="text-muted">فصيلة الدم:</small><br>
                                    <span id="viewUserBlood" class="badge bg-danger"></span>
                                </p>

                                <p class="mb-2"><small class="text-muted">تاريخ التسجيل:</small><br>
                                    <span id="viewUserCreated"></span>
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>


@endsection