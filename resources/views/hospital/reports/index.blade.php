@extends('hospital.layouts.hospital')

@section('title', 'التقارير والإحصائيات')

@section('content')
@php
    $stats = $reportData['stats'];
    $summary = $reportData['summary'];
    $filtersStart = $filters['start']->format('Y-m-d');
    $filtersEnd = $filters['end']->format('Y-m-d');
    $statusMeta = [
        'pending' => ['label' => 'قيد المراجعة', 'badge' => 'bg-warning'],
        'approved' => ['label' => 'مقبول', 'badge' => 'bg-primary'],
        'in_progress' => ['label' => 'جاري التبرع', 'badge' => 'bg-purple'],
        'completed' => ['label' => 'مكتمل', 'badge' => 'bg-success'],
        'rejected' => ['label' => 'مرفوض', 'badge' => 'bg-danger'],
    ];
@endphp

<main id="mainContent" class="main-content">
    <div class="content-wrapper">
        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form id="hospitalReportsFilterForm" method="GET" action="{{ route('hospital.reports.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filtersStart }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filtersEnd }}">
                        </div>
                        <div class="col-md-4">
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
                            <small class="text-muted">إجمالي طلبات الدم</small>
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
                            <small class="text-muted">طلبات مكتملة</small>
                            <h3 class="mb-0 fw-bold" id="stat-completed-requests">
                                {{ number_format($stats['completed_requests']['value']) }}
                            </h3>
                            @php
                                $change = $stats['completed_requests']['change'];
                                $trendClass = $change['trend'] === 'up' ? 'text-success' : ($change['trend'] === 'down' ? 'text-danger' : 'text-muted');
                                $trendIcon = $change['trend'] === 'up' ? 'fa-arrow-up' : ($change['trend'] === 'down' ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <small id="stat-completed-requests-change" class="{{ $trendClass }}">
                                <i class="fas {{ $trendIcon }}"></i>
                                <span>{{ $change['text'] }}</span>
                            </small>
                        </div>
                        <div class="stat-icon bg-green">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">التبرعات المكتملة</small>
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
                        <div class="stat-icon bg-red">
                            <i class="fas fa-handshake-angle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">عدد المتبرعين</small>
                            <h3 class="mb-0 fw-bold" id="stat-unique-donors">
                                {{ number_format($stats['unique_donors']['value']) }}
                            </h3>
                            @php
                                $change = $stats['unique_donors']['change'];
                                $trendClass = $change['trend'] === 'up' ? 'text-success' : ($change['trend'] === 'down' ? 'text-danger' : 'text-muted');
                                $trendIcon = $change['trend'] === 'up' ? 'fa-arrow-up' : ($change['trend'] === 'down' ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <small id="stat-unique-donors-change" class="{{ $trendClass }}">
                                <i class="fas {{ $trendIcon }}"></i>
                                <span>{{ $change['text'] }}</span>
                            </small>
                        </div>
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-primary ms-2"></i>
                            حركة الطلبات والتبرعات اليومية
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" height="90"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card custom-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group text-danger ms-2"></i>
                            حالات الطلبات خلال الفترة
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="180"></canvas>
                        <div class="mt-3" id="statusBreakdownList">
                            @foreach ($statusMeta as $key => $meta)
                                <div class="d-flex justify-content-between align-items-center {{ $loop->last ? '' : 'mb-2 pb-2 border-bottom' }}">
                                    <span>{{ $meta['label'] }}</span>
                                    <span class="badge {{ $meta['badge'] }}">
                                        {{ $reportData['status_breakdown'][$key] ?? 0 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card custom-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-info ms-2"></i>
                            توزيع فصائل الدم المطلوبة
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="bloodTypesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card custom-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-signal text-success ms-2"></i>
                            مؤشرات إضافية
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 border rounded-3 text-center">
                                    <div class="text-muted small">الوحدات المطلوبة</div>
                                    <div class="fw-bold fs-4" id="summary-requested-units">
                                        {{ number_format($summary['requested_units']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded-3 text-center">
                                    <div class="text-muted small">الوحدات المتبرع بها</div>
                                    <div class="fw-bold fs-4" id="summary-donated-units">
                                        {{ number_format($summary['donated_units']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded-3 text-center">
                                    <div class="text-muted small">نسبة اكتمال الطلبات</div>
                                    <div class="fw-bold fs-4" id="summary-completion-rate">
                                        {{ $summary['completion_rate'] }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded-3 text-center">
                                    <div class="text-muted small">طلبات قيد المتابعة خلال الفترة</div>
                                    <div class="fw-bold fs-4" id="summary-active-requests">
                                        {{ number_format($summary['active_requests']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-3" id="reports-last-updated">
                            آخر تحديث: {{ $reportData['last_updated'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Donors -->
        <div class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-trophy text-warning ms-2"></i>
                            أفضل المتبرعين خلال الفترة
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
                                <div class="text-end">
                                    <span class="badge bg-success">{{ $donor['total_units'] }} وحدة</span>
                                    <div class="small text-muted mt-1">{{ $donor['total_donations'] }} تبرع</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">لا توجد بيانات حالياً.</p>
                        @endforelse
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
const dataEndpoint = @json(route('hospital.reports.data'));

const statusOrder = [
    { key: 'pending', label: 'قيد المراجعة', color: '#FF9800', badge: 'bg-warning' },
    { key: 'approved', label: 'مقبول', color: '#2196F3', badge: 'bg-primary' },
    { key: 'in_progress', label: 'جاري التبرع', color: '#9C27B0', badge: 'bg-purple' },
    { key: 'completed', label: 'مكتمل', color: '#4CAF50', badge: 'bg-success' },
    { key: 'rejected', label: 'مرفوض', color: '#F44336', badge: 'bg-danger' }
];

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

const renderStatusBreakdown = (breakdown = {}) => {
    const container = document.getElementById('statusBreakdownList');
    if (!container) return;

    container.innerHTML = statusOrder.map((status, index) => {
        const count = Number(breakdown[status.key] || 0);
        const borderClass = index === statusOrder.length - 1 ? '' : 'mb-2 pb-2 border-bottom';
        return `
            <div class="d-flex justify-content-between align-items-center ${borderClass}">
                <span>${status.label}</span>
                <span class="badge ${status.badge}">${formatNumber(count)}</span>
            </div>
        `;
    }).join('');
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
                <div class="text-end">
                    <span class="badge bg-success">${formatNumber(donor.total_units)} وحدة</span>
                    <div class="small text-muted mt-1">${formatNumber(donor.total_donations)} تبرع</div>
                </div>
            </div>
        `;
    }).join('');
};

const updateSummary = (summary) => {
    const requestedEl = document.getElementById('summary-requested-units');
    const donatedEl = document.getElementById('summary-donated-units');
    const completionEl = document.getElementById('summary-completion-rate');
    const activeEl = document.getElementById('summary-active-requests');
    const updatedEl = document.getElementById('reports-last-updated');

    if (requestedEl) requestedEl.textContent = formatNumber(summary.requested_units);
    if (donatedEl) donatedEl.textContent = formatNumber(summary.donated_units);
    if (completionEl) completionEl.textContent = `${summary.completion_rate}%`;
    if (activeEl) activeEl.textContent = formatNumber(summary.active_requests);
    if (updatedEl && summary.last_updated) updatedEl.textContent = `آخر تحديث: ${summary.last_updated}`;
};

const charts = {};

const initCharts = (chartsData = {}, breakdown = {}) => {
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    charts.activity = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: chartsData.activity.labels || [],
            datasets: [
                {
                    label: 'طلبات الدم',
                    data: chartsData.activity.requests || [],
                    borderColor: '#1976D2',
                    backgroundColor: 'rgba(25, 118, 210, 0.15)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'التبرعات',
                    data: chartsData.activity.donations || [],
                    borderColor: '#8B0000',
                    backgroundColor: 'rgba(139, 0, 0, 0.12)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    charts.status = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusOrder.map((item) => item.label),
            datasets: [{
                data: statusOrder.map((item) => Number(breakdown[item.key] || 0)),
                backgroundColor: statusOrder.map((item) => item.color)
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const bloodCtx = document.getElementById('bloodTypesChart').getContext('2d');
    charts.bloodTypes = new Chart(bloodCtx, {
        type: 'doughnut',
        data: {
            labels: chartsData.blood_types.labels || [],
            datasets: [{
                data: chartsData.blood_types.data || [],
                backgroundColor: ['#D32F2F', '#F44336', '#1976D2', '#FF9800', '#4CAF50', '#9C27B0', '#607D8B', '#795548']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
};

const updateCharts = (chartsData = {}, breakdown = {}) => {
    if (charts.activity) {
        charts.activity.data.labels = chartsData.activity.labels || [];
        charts.activity.data.datasets[0].data = chartsData.activity.requests || [];
        charts.activity.data.datasets[1].data = chartsData.activity.donations || [];
        charts.activity.update();
    }

    if (charts.status) {
        charts.status.data.datasets[0].data = statusOrder.map((item) => Number(breakdown[item.key] || 0));
        charts.status.update();
    }

    if (charts.bloodTypes) {
        charts.bloodTypes.data.labels = chartsData.blood_types.labels || [];
        charts.bloodTypes.data.datasets[0].data = chartsData.blood_types.data || [];
        charts.bloodTypes.update();
    }
};

const fetchReportData = async () => {
    const form = document.getElementById('hospitalReportsFilterForm');
    if (!form) return;

    const params = new URLSearchParams(new FormData(form));
    const url = `${dataEndpoint}?${params.toString()}`;

    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
        if (!response.ok) return;

        const data = await response.json();
        updateStat('total-requests', data.stats.total_requests);
        updateStat('completed-requests', data.stats.completed_requests);
        updateStat('total-donations', data.stats.total_donations);
        updateStat('unique-donors', data.stats.unique_donors);
        renderStatusBreakdown(data.status_breakdown);
        renderTopDonors(data.top_donors);
        updateSummary({ ...data.summary, last_updated: data.last_updated });
        updateCharts(data.charts, data.status_breakdown);
    } catch (error) {
        console.warn('Failed to refresh hospital report data', error);
    }
};

initCharts(reportData.charts, reportData.status_breakdown);
renderStatusBreakdown(reportData.status_breakdown);
renderTopDonors(reportData.top_donors);
updateSummary({ ...reportData.summary, last_updated: reportData.last_updated });

setInterval(fetchReportData, 30000);
</script>
@endpush
