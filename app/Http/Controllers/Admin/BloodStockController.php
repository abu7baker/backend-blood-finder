<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodStock;
use App\Models\Hospital;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class BloodStockController extends Controller
{
    use LogsActivity;

    /**
     * عرض صفحة إدارة المخزون
     */
    public function index()
    {
        $hospitals = Hospital::with('bloodStock')->get();

        $bloodTypes = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

        $stats = [
            'o_pos'           => BloodStock::where('blood_type', 'O+')->sum('units_available'),
            'a_pos'           => BloodStock::where('blood_type', 'A+')->sum('units_available'),
            'b_pos'           => BloodStock::where('blood_type', 'B+')->sum('units_available'),
            'ab_pos'          => BloodStock::where('blood_type', 'AB+')->sum('units_available'),
            'total_available' => BloodStock::sum('units_available'),
            'total_expired'   => BloodStock::sum('units_expired'),
            'active_requests' => 0,
        ];

        $lowStocks = BloodStock::with('hospital')
            ->where('units_available', '<', 5)
            ->orderBy('units_available')
            ->get();

        $this->logActivity(
            'view',
            'عرض صفحة إدارة مخزون الدم'
        );

        return view('admin.inventory.index', compact(
            'hospitals',
            'bloodTypes',
            'stats',
            'lowStocks'
        ));
    }

    /**
     * إضافة مخزون جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id'     => 'required|exists:hospitals,id',
            'blood_type'      => 'required',
            'units_available' => 'required|integer|min:0',
        ]);

        $hospital = Hospital::findOrFail($request->hospital_id);

        $stock = BloodStock::create([
            'hospital_id'     => $hospital->id,
            'blood_type'      => $request->blood_type,
            'units_available' => $request->units_available,
            'units_reserved'  => 0,
            'units_expired'   => 0,
            'status'          => 'available',
        ]);

        $this->logActivity(
            'create',
            'إضافة مخزون دم جديد للمستشفى: ' . $hospital->name .
            ' (الفصيلة: ' . $stock->blood_type .
            ' – الكمية: ' . $stock->units_available . ')'
        );

        return back()->with('success', 'تم إضافة المخزون بنجاح');
    }

    /**
     * تفاصيل مخزون مستشفى
     */
    public function details($hospital_id)
    {
        $hospital = Hospital::with('bloodStock')->findOrFail($hospital_id);

        $this->logActivity(
            'view',
            'عرض تفاصيل مخزون مستشفى: ' . $hospital->name
        );

        return view('admin.inventory.details', compact('hospital'));
    }

    /**
     * تحديث مخزون محدد
     */
    public function update(Request $request, $id)
    {
        $stock = BloodStock::with('hospital')->findOrFail($id);

        $oldAvailable = $stock->units_available;
        $oldReserved  = $stock->units_reserved;
        $oldExpired   = $stock->units_expired;

        $request->validate([
            'units_available' => 'required|integer|min:0',
            'units_reserved'  => 'required|integer|min:0',
            'units_expired'   => 'required|integer|min:0',
        ]);

        $stock->update($request->all());

        $changes = [];

        if ($oldAvailable != $stock->units_available) {
            $changes[] = 'المتاح: ' . $oldAvailable . ' → ' . $stock->units_available;
        }

        if ($oldReserved != $stock->units_reserved) {
            $changes[] = 'المحجوز: ' . $oldReserved . ' → ' . $stock->units_reserved;
        }

        if ($oldExpired != $stock->units_expired) {
            $changes[] = 'المنتهي: ' . $oldExpired . ' → ' . $stock->units_expired;
        }

        if (!empty($changes)) {
            $this->logActivity(
                'update',
                'تحديث مخزون الدم (' . $stock->blood_type . ') للمستشفى: ' .
                $stock->hospital->name . '<br>' .
                implode('<br>', $changes)
            );
        }

        return back()->with('success', 'تم تحديث المخزون بنجاح');
    }

    /**
     * تحديث أو إنشاء مخزون (التحديث السريع)
     */
    public function updateStock(Request $request)
    {
        $request->validate([
            'hospital_id'     => 'required|exists:hospitals,id',
            'blood_type'      => 'required',
            'units_available' => 'required|integer|min:0',
        ]);

        $hospital = Hospital::findOrFail($request->hospital_id);

        $stock = BloodStock::updateOrCreate(
            [
                'hospital_id' => $hospital->id,
                'blood_type'  => $request->blood_type,
            ],
            [
                'units_available' => $request->units_available,
            ]
        );

        $this->logActivity(
            'update',
            'تحديث سريع لمخزون الدم (' . $stock->blood_type .
            ') للمستشفى: ' . $hospital->name .
            ' – الكمية الجديدة: ' . $stock->units_available
        );

        return back()->with('success', 'تم تحديث المخزون بنجاح ✔️');
    }

    /**
     * حذف مخزون
     */
    public function destroy($id)
    {
        $stock = BloodStock::with('hospital')->findOrFail($id);

        $this->logActivity(
            'delete',
            'حذف مخزون الدم (' . $stock->blood_type .
            ') من المستشفى: ' . $stock->hospital->name
        );

        $stock->delete();

        return back()->with('success', 'تم حذف المخزون');
    }
}
