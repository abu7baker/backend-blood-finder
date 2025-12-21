<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\BloodStock;
use App\Models\BloodRequest;
use Illuminate\Support\Facades\Auth;

class HospitalStockController extends Controller
{
    public function index()
    {
        $hospital = Auth::user()->hospital;

        // مخزون المستشفى
        $stocks = BloodStock::where('hospital_id', $hospital->id)->get();

        // حساب الإحصائيات
        $stats = [
            'total' => $stocks->sum('units_available'),

            'expired' => $stocks->where('expires_at', '<', now())->count(),

            'active_requests' => BloodRequest::where('hospital_id', $hospital->id)
                                ->where('status', 'approved')
                                ->count(),

            'by_type' => $stocks->groupBy('blood_type')->map->sum('units_available'),

            'low_stock' => $stocks->filter(fn($s) => $s->units_available <= 3)
                            ->map(fn($s) => [
                                'type' => $s->blood_type,
                                'count' => $s->units_available
                            ])->values()
        ];

        return view('hospital.inventory.index', compact('stocks', 'stats'));
    }
}
