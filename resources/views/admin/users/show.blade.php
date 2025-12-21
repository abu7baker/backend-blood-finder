@extends('layouts.admin')

@section('title', 'تفاصيل المستخدم')

@section('content')

<main id="mainContent" class="main-content">

    <div class="content-wrapper">

        <div class="card custom-card">

            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-user text-primary ms-2"></i>
                    تفاصيل المستخدم
                </h5>

                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-arrow-right ms-1"></i> العودة
                </a>
            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-4 text-center">
                        <div class="admin-avatar"
                             style="width:110px;height:110px;font-size:2.5rem;margin:auto">
                            {{ mb_substr($user->full_name, 0, 1) }}
                        </div>

                        <h4 class="mt-3 fw-bold">{{ $user->full_name }}</h4>

                        @if($user->status === 'active')
                            <span class="status-badge status-active">نشط</span>
                        @elseif($user->status === 'pending')
                            <span class="status-badge status-pending">قيد المراجعة</span>
                        @else
                            <span class="status-badge status-blocked">محظور</span>
                        @endif
                    </div>

                    <div class="col-md-8">

                        <div class="p-3 rounded bg-light">
                            <p class="mb-2"><small class="text-muted">البريد الإلكتروني:</small><br>
                                {{ $user->email ?? '—' }}
                            </p>

                            <p class="mb-2"><small class="text-muted">الهاتف:</small><br>
                                {{ $user->phone }}
                            </p>


                             <p class="mb-2"><small class="text-muted">الفصيلة:</small><br>
                                <span class="badge bg-danger">{{ $user->blood_type ?? 'لا يوجد'}}</span>
                            </p>
                        
                                
                          
                            <p class="mb-2"><small class="text-muted">المدينة:</small><br>
                                {{ $user->city ?? '—' }}
                            </p>
                            

                            <p class="mb-2"><small class="text-muted">النوع:</small><br>
                                 @if($user->role->name === 'admin')
                                        <span class="badge bg-dark">مدير</span>
                                    @elseif($user->role->name === 'hospital')
                                        <span class="badge bg-primary">مستشفى</span>
                                    @else
                                        <span class="badge bg-danger">مستخدم</span>
                                    @endif
                            </p>

                            <p class="mb-2"><small class="text-muted">تاريخ التسجيل:</small><br>
                                {{ $user->created_at->format('Y-m-d') }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

@endsection
