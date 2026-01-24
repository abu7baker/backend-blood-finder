<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HospitalReportsController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $hospital = $this->getHospital();
        $filters = $this->resolveFilters($request);
        $reportData = $this->buildReportData($filters, $hospital->id);

        $this->logActivity(
            'view',
            'عرض صفحة التقارير والاحصائيات للمستشفى: ' . ($hospital->name ?? 'غير محدد')
        );

        return view('hospital.reports.index', [
            'filters' => $filters,
            'reportData' => $reportData,
            'hospital' => $hospital,
        ]);
    }

    public function data(Request $request)
    {
        $hospital = $this->getHospital();
        $filters = $this->resolveFilters($request);

        return response()->json($this->buildReportData($filters, $hospital->id));
    }

    private function getHospital()
    {
        $hospital = auth()->user()?->hospital;

        if (!$hospital) {
            abort(403, 'هذا الحساب غير مرتبط بأي مستشفى.');
        }

        return $hospital;
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

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function buildReportData(array $filters, int $hospitalId): array
    {
        $start = $filters['start'];
        $end = $filters['end'];

        $requestsBase = BloodRequest::query()->where('hospital_id', $hospitalId);
        $donationsBase = Donation::query()->where('hospital_id', $hospitalId);

        $requestsRange = (clone $requestsBase)->whereBetween('created_at', [$start, $end]);
        $completedRequestsRange = (clone $requestsBase)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end]);

        $completedDonationsRange = (clone $donationsBase)
            ->where('donations.status', 'completed')
            ->whereBetween('donations.donated_at', [$start, $end]);

        $stats = [
            'total_requests' => (clone $requestsRange)->count(),
            'completed_requests' => (clone $completedRequestsRange)->count(),
            'total_donations' => (clone $completedDonationsRange)->count(),
            'unique_donors' => (clone $completedDonationsRange)->distinct('donor_id')->count('donor_id'),
        ];

        $previousRange = $this->previousRange($start, $end);

        $previousStats = [
            'total_requests' => (clone $requestsBase)
                ->whereBetween('created_at', [$previousRange['start'], $previousRange['end']])
                ->count(),
            'completed_requests' => (clone $requestsBase)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$previousRange['start'], $previousRange['end']])
                ->count(),
            'total_donations' => (clone $donationsBase)
                ->where('donations.status', 'completed')
                ->whereBetween('donations.donated_at', [$previousRange['start'], $previousRange['end']])
                ->count(),
            'unique_donors' => (clone $donationsBase)
                ->where('donations.status', 'completed')
                ->whereBetween('donations.donated_at', [$previousRange['start'], $previousRange['end']])
                ->distinct('donor_id')
                ->count('donor_id'),
        ];

        $statsWithChange = [];
        foreach ($stats as $key => $current) {
            $statsWithChange[$key] = [
                'value' => $current,
                'change' => $this->calculateChange($current, $previousStats[$key] ?? 0),
            ];
        }

        $requestedUnits = (clone $requestsRange)->sum('units_requested');
        $donatedUnits = (clone $completedDonationsRange)->sum('units_donated');

        $statusCounts = (clone $requestsRange)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $requestsByDay = (clone $requestsRange)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $donationsByDay = (clone $completedDonationsRange)
            ->selectRaw('DATE(donated_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $days = [];
        $requestsSeries = [];
        $donationsSeries = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $label = $cursor->format('Y-m-d');
            $days[] = $cursor->format('m-d');
            $requestsSeries[] = (int) ($requestsByDay[$label] ?? 0);
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

        $topDonors = (clone $completedDonationsRange)
            ->join('users', 'donations.donor_id', '=', 'users.id')
            ->select('users.full_name', 'users.city', 'users.blood_type')
            ->selectRaw('SUM(donations.units_donated) as total_units')
            ->selectRaw('COUNT(*) as total_donations')
            ->groupBy('users.id', 'users.full_name', 'users.city', 'users.blood_type')
            ->orderByDesc('total_units')
            ->limit(6)
            ->get()
            ->map(function ($donor) {
                return [
                    'full_name' => $donor->full_name,
                    'city' => $donor->city ?: 'غير محدد',
                    'blood_type' => $donor->blood_type ?: '-',
                    'total_units' => (int) $donor->total_units,
                    'total_donations' => (int) $donor->total_donations,
                ];
            })
            ->values();

        $completionRate = $stats['total_requests'] > 0
            ? round(($stats['completed_requests'] / $stats['total_requests']) * 100, 1)
            : 0;

        return [
            'stats' => $statsWithChange,
            'summary' => [
                'requested_units' => (int) $requestedUnits,
                'donated_units' => (int) $donatedUnits,
                'completion_rate' => $completionRate,
                'active_requests' => (clone $requestsRange)
                    ->whereIn('status', ['pending', 'approved', 'in_progress'])
                    ->count(),
            ],
            'status_breakdown' => $statusCounts,
            'charts' => [
                'activity' => [
                    'labels' => $days,
                    'requests' => $requestsSeries,
                    'donations' => $donationsSeries,
                ],
                'blood_types' => [
                    'labels' => $bloodTypeLabels,
                    'data' => $bloodTypeSeries,
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
