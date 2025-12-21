@extends('hospital.layouts.hospital')

@section('title', 'لوحة تحكم المستشفى')

@section('content')

<main id="mainContent" class="main-content">

    <!-- Content -->
    <div class="content-wrapper">

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">

            {{-- إجمالي طلبات الدم --}}
            <div class="col-md-6 col-lg-3">
                <div class="stat-card stat-card-blue">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <small class="text-muted">إجمالي طلبات الدم</small>
                            <div class="d-flex align-items-center gap-2">
                               <h3>{{ $requests_count }}</h3>
                            </div>
                        </div>
                        <div class="stat-icon bg-blue">
                              <i class="fas fa-inbox"></i>
                        </div>
                    </div>
                </div>
            </div>

          

            {{-- مواعيد التبرع --}}
            <div class="col-md-6 col-lg-3">
                <div class="stat-card stat-card-red">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <small class="text-muted">وحدات الدم المتاحة</small>
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="mb-0 fw-bold">{{ $stock_count }}</h3>
                               </div>
                        </div>
                        <div class="stat-icon bg-red">
                            <i class="fas fa-droplet"></i>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-md-6 col-lg-3">
                <div class="stat-card stat-card-red">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <small class="text-muted">مواعيد التبرع</small>
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="mb-0 fw-bold">{{ $appointments_count }}</h3>
                               </div>
                        </div>
                        <div class="stat-icon bg-green">
                          <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{--مواعيد التبرع الجديدة --}}
            <div class="col-md-6 col-lg-3">
                <div class="stat-card stat-card-purple">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <small class="text-muted">الإشعارات الجديدة</small>
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="mb-0 fw-bold">{{ $notifications_count }}</h3>
                            
                            </div>
                        </div>
                        <div class="stat-icon bg-purple">
                        <i class="fas fa-bell"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>



        
        <!-- Today Stats -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day text-danger ms-2"></i>
                    إحصائيات اليوم
                </h5>
            </div>

            <div class="card-body">
                <div class="row g-4 text-center">

                    <div class="col-6 col-md-3">
                        <div class="today-stat-icon bg-green-subtle">
                            <i class="fas fa-handshake-angle text-green"></i>
                        </div>
                        <h4 class="fw-bold mt-2"></h4>
                        <small class="text-muted">تبرعات اليوم</small>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="today-stat-icon bg-blue-subtle">
                            <i class="fas fa-file-lines text-blue"></i>
                        </div>
                        <h4 class="fw-bold mt-2"></h4>
                        <small class="text-muted">طلبات جديدة</small>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="today-stat-icon bg-purple-subtle">
                            <i class="fas fa-chart-line text-purple"></i>
                        </div>
                        <h4 class="fw-bold mt-2"></h4>
                        <small class="text-muted">مستشفيات نشطة</small>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="today-stat-icon bg-red-subtle">
                            <i class="fas fa-triangle-exclamation text-red"></i>
                        </div>
                        <h4 class="fw-bold mt-2"></h4>
                        <small class="text-muted">حالات طوارئ</small>
                    </div>

                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        
    </div>
</main>

@endsection
