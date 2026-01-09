@extends('layouts.admin')

@section('title', 'التقارير والإحصائيات')

@section('content')
@php
    $stats = $reportData['stats'];
    $filtersStart = $filters['start']->format('Y-m-d');
    $filtersEnd = $filters['end']->format('Y-m-d');
    $filtersHospital = $filters['hospital_id'];
@endphp

<main id="mainContent" class="main-content">
    <div class="content-wrapper">
        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form id="reportsFilterForm" method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filtersStart }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filtersEnd }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">المستشفى</label>
                            <select name="hospital_id" class="form-select">
                                <option value="all">كل المستشفيات</option>
                                @foreach ($hospitals as $hospital)
                                    <option value="{{ $hospital->id }}"
                                        {{ (string) $filtersHospital === (string) $hospital->id ? 'selected' : '' }}>
                                        {{ $hospital->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fas fa-filter ms-2"></i>تطبيق
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">إجمالي التبرعات</small>
                            <h3 class="mb-0 fw-bold" id="stat-total-donations">
                                {{ number_format($stats['total_donations']['value']) }}
                            </h3>
                            @php
                                $change = $stats['total_donations']['change'];
                                $trendClass = $change['trend'] === 'up' ? 'text-success' : ($change['trend'] === 'down' ? 'text-danger' : 'text-muted');
                                $trendIcon = $change['trend'] === 'up' ? 'fa-arrow-up' : ($change['trend'] === 'down' ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <small id="stat-total-donations-change" class="{{ $trendClass }}">
                                <i class="fas {{ $trendIcon }}"></i>
                                <span>{{ $change['text'] }}</span>
                            </small>
                        </div>
                        <div class="stat-icon bg-green">
                            <i class="fas fa-handshake-angle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">طلبات الدم</small>
                            <h3 class="mb-0 fw-bold" id="stat-total-requests">
                                {{ number_format($stats['total_requests']['value']) }}
                            </h3>
                            @php
                                $change = $stats['total_requests']['change'];
                                $trendClass = $change['trend'] === 'up' ? 'text-success' : ($change['trend'] === 'down' ? 'text-danger' : 'text-muted');
                                $trendIcon = $change['trend'] === 'up' ? 'fa-arrow-up' : ($change['trend'] === 'down' ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <small id="stat-total-requests-change" class="{{ $trendClass }}">
                                <i class="fas {{ $trendIcon }}"></i>
                                <span>{{ $change['text'] }}</span>
                            </small>
                        </div>
                        <div class="stat-icon bg-blue">
                            <i class="fas fa-file-medical"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">متبرعون جدد</small>
                            <h3 class="mb-0 fw-bold" id="stat-new-donors">
                                {{ number_format($stats['new_donors']['value']) }}
                            </h3>
                            @php
                                $change = $stats['new_donors']['change'];
                                $trendClass = $change['trend'] === 'up' ? 'text-success' : ($change['trend'] === 'down' ? 'text-danger' : 'text-muted');
                                $trendIcon = $change['trend'] === 'up' ? 'fa-arrow-up' : ($change['trend'] === 'down' ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <small id="stat-new-donors-change" class="{{ $trendClass }}">
                                <i class="fas {{ $trendIcon }}"></i>
                                <span>{{ $change['text'] }}</span>
                            </small>
                        </div>
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">طلبات عاجلة</small>
                            <h3 class="mb-0 fw-bold" id="stat-urgent-requests">
                                {{ number_format($stats['urgent_requests']['value']) }}
                            </h3>
                            @php
                                $change = $stats['urgent_requests']['change'];
                                $trendClass = $change['trend'] === 'up' ? 'text-success' : ($change['trend'] === 'down' ? 'text-danger' : 'text-muted');
                                $trendIcon = $change['trend'] === 'up' ? 'fa-arrow-up' : ($change['trend'] === 'down' ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <small id="stat-urgent-requests-change" class="{{ $trendClass }}">
                                <i class="fas {{ $trendIcon }}"></i>
                                <span>{{ $change['text'] }}</span>
                            </small>
                        </div>
                        <div class="stat-icon bg-red">
                            <i class="fas fa-ambulance"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-primary ms-2"></i>
                            حركة التبرعات اليومية
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donationsChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-danger ms-2"></i>
                            توزيع فصائل الدم
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="bloodTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-hospital text-info ms-2"></i>
                            أكثر المستشفيات نشاطاً
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="hospitalsChart" height="110"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Donors & Export -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-trophy text-warning ms-2"></i>
                            أفضل المتبرعين
                        </h5>
                    </div>
                    <div class="card-body" id="top-donors-list">
                        @forelse ($reportData['top_donors'] as $donor)
                            <div class="d-flex justify-content-between align-items-center {{ $loop->last ? '' : 'mb-3 pb-3 border-bottom' }}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon bg-orange" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $donor['full_name'] }}</strong>
                                        <p class="mb-0 small text-muted">{{ $donor['city'] }} - {{ $donor['blood_type'] }}</p>
                                    </div>
                                </div>
                                <span class="badge bg-success">{{ $donor['total'] }} تبرع</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">لا توجد بيانات حالياً.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-download text-success ms-2"></i>
                            تصدير التقارير
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <button class="btn btn-outline-success d-flex justify-content-between align-items-center" disabled>
                                <span><i class="fas fa-file-excel ms-2"></i>تقرير التبرعات الشهري</span>
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-outline-primary d-flex justify-content-between align-items-center" disabled>
                                <span><i class="fas fa-file-pdf ms-2"></i>تقرير المستشفيات</span>
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-outline-danger d-flex justify-content-between align-items-center" disabled>
                                <span><i class="fas fa-file-csv ms-2"></i>تقرير المخزون</span>
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-outline-warning d-flex justify-content-between align-items-center" disabled>
                                <span><i class="fas fa-file-alt ms-2"></i>تقرير المتبرعين</span>
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-outline-info d-flex justify-content-between align-items-center" disabled>
                                <span><i class="fas fa-file-invoice ms-2"></i>التقرير السنوي الكامل</span>
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">ميزة التصدير ستُفعّل قريباً.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const reportData = @json($reportData);
const dataEndpoint = @json(route('admin.reports.data'));

const trendMeta = (trend) => {
    if (trend === 'up') return { cls: 'text-success', icon: 'fa-arrow-up' };
    if (trend === 'down') return { cls: 'text-danger', icon: 'fa-arrow-down' };
    return { cls: 'text-muted', icon: 'fa-minus' };
};

const formatNumber = (value) => new Intl.NumberFormat('ar').format(value ?? 0);

const updateStat = (key, data) => {
    const valueEl = document.getElementById(`stat-${key}`);
    const changeEl = document.getElementById(`stat-${key}-change`);
    if (!valueEl || !changeEl) return;

    valueEl.textContent = formatNumber(data.value);
    const meta = trendMeta(data.change.trend);
    changeEl.className = meta.cls;
    const icon = changeEl.querySelector('i');
    const text = changeEl.querySelector('span');
    if (icon) icon.className = `fas ${meta.icon}`;
    if (text) text.textContent = data.change.text;
};

const renderTopDonors = (donors) => {
    const container = document.getElementById('top-donors-list');
    if (!container) return;

    if (!donors || donors.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">لا توجد بيانات حالياً.</p>';
        return;
    }

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (m) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[m]);

    container.innerHTML = donors.map((donor, index) => {
        const borderClass = index === donors.length - 1 ? '' : 'mb-3 pb-3 border-bottom';
        return `
            <div class="d-flex justify-content-between align-items-center ${borderClass}">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-orange" style="width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div>
                        <strong>${escapeHtml(donor.full_name)}</strong>
                        <p class="mb-0 small text-muted">${escapeHtml(donor.city)} - ${escapeHtml(donor.blood_type)}</p>
                    </div>
                </div>
                <span class="badge bg-success">${formatNumber(donor.total)} تبرع</span>
            </div>
        `;
    }).join('');
};

const charts = {};

const initCharts = (chartsData) => {
    const donationsCtx = document.getElementById('donationsChart').getContext('2d');
    charts.donations = new Chart(donationsCtx, {
        type: 'line',
        data: {
            labels: chartsData.donations.labels,
            datasets: [{
                label: 'التبرعات',
                data: chartsData.donations.data,
                borderColor: '#8B0000',
                backgroundColor: 'rgba(139, 0, 0, 0.12)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } }
        }
    });

    const bloodTypesCtx = document.getElementById('bloodTypesChart').getContext('2d');
    charts.bloodTypes = new Chart(bloodTypesCtx, {
        type: 'doughnut',
        data: {
            labels: chartsData.blood_types.labels,
            datasets: [{
                data: chartsData.blood_types.data,
                backgroundColor: ['#D32F2F', '#F44336', '#1976D2', '#FF9800', '#4CAF50', '#9C27B0', '#607D8B', '#795548']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const hospitalsCtx = document.getElementById('hospitalsChart').getContext('2d');
    charts.hospitals = new Chart(hospitalsCtx, {
        type: 'bar',
        data: {
            labels: chartsData.hospitals.labels,
            datasets: [{
                label: 'التبرعات',
                data: chartsData.hospitals.data,
                backgroundColor: 'rgba(33, 150, 243, 0.7)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
};

const updateCharts = (chartsData) => {
    if (charts.donations) {
        charts.donations.data.labels = chartsData.donations.labels;
        charts.donations.data.datasets[0].data = chartsData.donations.data;
        charts.donations.update();
    }

    if (charts.bloodTypes) {
        charts.bloodTypes.data.labels = chartsData.blood_types.labels;
        charts.bloodTypes.data.datasets[0].data = chartsData.blood_types.data;
        charts.bloodTypes.update();
    }

    if (charts.hospitals) {
        charts.hospitals.data.labels = chartsData.hospitals.labels;
        charts.hospitals.data.datasets[0].data = chartsData.hospitals.data;
        charts.hospitals.update();
    }
};

const fetchReportData = async () => {
    const form = document.getElementById('reportsFilterForm');
    if (!form) return;

    const params = new URLSearchParams(new FormData(form));
    const url = `${dataEndpoint}?${params.toString()}`;

    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
        if (!response.ok) return;

        const data = await response.json();
        updateStat('total-donations', data.stats.total_donations);
        updateStat('total-requests', data.stats.total_requests);
        updateStat('new-donors', data.stats.new_donors);
        updateStat('urgent-requests', data.stats.urgent_requests);
        renderTopDonors(data.top_donors);
        updateCharts(data.charts);
    } catch (error) {
        console.warn('Failed to refresh report data', error);
    }
};

initCharts(reportData.charts);
renderTopDonors(reportData.top_donors);

setInterval(fetchReportData, 30000);
</script>
@endpush
