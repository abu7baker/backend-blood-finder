<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\Hospital;
use App\Models\User;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $reportData = $this->buildReportData($filters);

        $hospitals = Hospital::orderBy('name')->get(['id', 'name']);

        $this->logActivity('view', 'عرض صفحة التقارير والإحصائيات');

        return view('admin.reports.index', [
            'filters' => $filters,
            'reportData' => $reportData,
            'hospitals' => $hospitals,
        ]);
    }

    public function data(Request $request)
    {
        $filters = $this->resolveFilters($request);

        return response()->json($this->buildReportData($filters));
    }

    private function resolveFilters(Request $request): array
    {
        $start = $request->query('start_date')
            ? Carbon::parse($request->query('start_date'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        $end = $request->query('end_date')
            ? Carbon::parse($request->query('end_date'))->endOfDay()
            : now()->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $hospitalId = $request->query('hospital_id');
        if ($hospitalId === 'all' || $hospitalId === null || $hospitalId === '') {
            $hospitalId = null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'hospital_id' => $hospitalId,
        ];
    }

    private function buildReportData(array $filters): array
    {
        $start = $filters['start'];
        $end = $filters['end'];
        $hospitalId = $filters['hospital_id'];

        $donationsBase = Donation::query()
            ->when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId));

        $requestsBase = BloodRequest::query()
            ->when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId));

        $donorsBase = User::query()->where('role_id', 3);

        $donationsRange = (clone $donationsBase)->whereBetween('donated_at', [$start, $end]);
        $requestsRange = (clone $requestsBase)->whereBetween('blood_requests.created_at', [$start, $end]);
        $donorsRange = (clone $donorsBase)->whereBetween('created_at', [$start, $end]);

        $stats = [
            'total_donations' => (clone $donationsRange)->count(),
            'total_requests' => (clone $requestsRange)->count(),
            'new_donors' => (clone $donorsRange)->count(),
            'urgent_requests' => (clone $requestsRange)
                ->whereIn('priority', ['urgent', 'critical'])
                ->count(),
        ];

        $previousRange = $this->previousRange($start, $end);

        $previousStats = [
            'total_donations' => (clone $donationsBase)
                ->whereBetween('donated_at', [$previousRange['start'], $previousRange['end']])
                ->count(),
            'total_requests' => (clone $requestsBase)
                ->whereBetween('created_at', [$previousRange['start'], $previousRange['end']])
                ->count(),
            'new_donors' => (clone $donorsBase)
                ->whereBetween('created_at', [$previousRange['start'], $previousRange['end']])
                ->count(),
            'urgent_requests' => (clone $requestsBase)
                ->whereBetween('created_at', [$previousRange['start'], $previousRange['end']])
                ->whereIn('priority', ['urgent', 'critical'])
                ->count(),
        ];

        $statsWithChange = [];
        foreach ($stats as $key => $current) {
            $statsWithChange[$key] = [
                'value' => $current,
                'change' => $this->calculateChange($current, $previousStats[$key] ?? 0),
            ];
        }

        $donationsByDay = (clone $donationsRange)
            ->selectRaw('DATE(donated_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $days = [];
        $donationsSeries = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $label = $cursor->format('Y-m-d');
            $days[] = $cursor->format('m-d');
            $donationsSeries[] = (int) ($donationsByDay[$label] ?? 0);
            $cursor->addDay();
        }

        $bloodTypes = (clone $requestsRange)
            ->selectRaw('blood_type, SUM(units_requested) as total')
            ->groupBy('blood_type')
            ->pluck('total', 'blood_type');

        $bloodTypeLabels = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'];
        $bloodTypeSeries = [];
        foreach ($bloodTypeLabels as $label) {
            $bloodTypeSeries[] = (int) ($bloodTypes[$label] ?? 0);
        }

        $topHospitals = (clone $requestsRange)
            ->join('hospitals', 'blood_requests.hospital_id', '=', 'hospitals.id')
            ->select('hospitals.name')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('hospitals.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $hospitalLabels = $topHospitals->pluck('name')->values();
        $hospitalSeries = $topHospitals->pluck('total')->map(fn ($value) => (int) $value)->values();

        $topDonors = (clone $donationsRange)
            ->join('users', 'donations.donor_id', '=', 'users.id')
            ->select('users.full_name', 'users.city', 'users.blood_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('users.id', 'users.full_name', 'users.city', 'users.blood_type')
            ->orderByDesc('total')
            ->limit(4)
            ->get()
            ->map(function ($donor) {
                return [
                    'full_name' => $donor->full_name,
                    'city' => $donor->city ?: 'غير محدد',
                    'blood_type' => $donor->blood_type ?: '-',
                    'total' => (int) $donor->total,
                ];
            })
            ->values();

        return [
            'stats' => $statsWithChange,
            'charts' => [
                'donations' => [
                    'labels' => $days,
                    'data' => $donationsSeries,
                ],
                'blood_types' => [
                    'labels' => $bloodTypeLabels,
                    'data' => $bloodTypeSeries,
                ],
                'hospitals' => [
                    'labels' => $hospitalLabels,
                    'data' => $hospitalSeries,
                ],
            ],
            'top_donors' => $topDonors,
            'last_updated' => now()->toDateTimeString(),
        ];
    }

    private function previousRange(Carbon $start, Carbon $end): array
    {
        $days = $start->diffInDays($end) + 1;
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        return [
            'start' => $previousStart,
            'end' => $previousEnd,
        ];
    }

    private function calculateChange(int $current, int $previous): array
    {
        if ($previous === 0) {
            if ($current === 0) {
                return ['text' => '0%', 'trend' => 'flat'];
            }

            return ['text' => 'جديد', 'trend' => 'up'];
        }

        $diff = $current - $previous;
        $percent = round(($diff / $previous) * 100, 1);
        $trend = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat');
        $sign = $diff > 0 ? '+' : '';

        return ['text' => $sign . $percent . '%', 'trend' => $trend];
    }
}
