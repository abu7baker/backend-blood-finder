<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodStock;
use App\Models\Hospital;
use Illuminate\Http\Request;

class BloodStockController extends Controller
{
    public function index()
    {
        $hospitals = Hospital::with('bloodStock')->get();

        $bloodTypes = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

        // إحصائيات سريعة
        $stats = [
            'o_pos' => BloodStock::where('blood_type', 'O+')->sum('units_available'),
            'a_pos' => BloodStock::where('blood_type', 'A+')->sum('units_available'),
            'b_pos' => BloodStock::where('blood_type', 'B+')->sum('units_available'),
            'ab_pos' => BloodStock::where('blood_type', 'AB+')->sum('units_available'),
            'total_available' => BloodStock::sum('units_available'),
            'total_expired' => BloodStock::sum('units_expired'),
            'active_requests' => 0, // مؤقتاً 0 لو ما عندك جدول طلبات جاهز
        ];

        // تنبيهات النقص الحاد (أقل من 5 وحدات)
        $lowStocks = BloodStock::with('hospital')
            ->where('units_available', '<', 5)
            ->orderBy('units_available')
            ->get();

        return view('admin.inventory.index', compact('hospitals', 'bloodTypes', 'stats', 'lowStocks'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'blood_type' => 'required',
            'units_available' => 'required|integer|min:0',
        ]);

        BloodStock::create([
            'hospital_id' => $request->hospital_id,
            'blood_type' => $request->blood_type,
            'units_available' => $request->units_available,
            'units_reserved' => 0,
            'units_expired' => 0,
            'status' => 'available',
        ]);

        return back()->with('success', 'تم إضافة المخزون بنجاح');
    }


    public function details($hospital_id)
    {
        $hospital = Hospital::with('bloodStock')->findOrFail($hospital_id);

        return view('admin.inventory.details', compact('hospital'));
    }


    public function update(Request $request, $id)
    {
        $stock = BloodStock::findOrFail($id);

        $request->validate([
            'units_available' => 'required|integer|min:0',
            'units_reserved' => 'required|integer|min:0',
            'units_expired' => 'required|integer|min:0',
        ]);

        $stock->update($request->all());

        return back()->with('success', 'تم تحديث المخزون بنجاح');
    }

    public function updateStock(Request $request)
    {

        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'blood_type' => 'required',
            'units_available' => 'required|integer|min:0',
        ]);

        // تحديث إذا موجود – أو إنشاء إذا غير موجود
        $stock = BloodStock::updateOrCreate(
            [
                'hospital_id' => $request->hospital_id,
                'blood_type' => $request->blood_type,
            ],
            [
                'units_available' => $request->units_available,
            ]
        );

        return back()->with('success', 'تم تحديث المخزون بنجاح ✔️');
    }

    ////
    public function destroy($id)
    {
        $stock = BloodStock::findOrFail($id);
        $stock->delete();

        return back()->with('success', 'تم حذف المخزون');
    }
}
