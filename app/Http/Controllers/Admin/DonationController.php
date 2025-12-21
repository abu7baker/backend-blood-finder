<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\BloodRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    /**
     * 📌 عرض التبرعات + الإحصائيات (أدمن + مستشفى)
     */
    public function index(Request $request)
    {
        // ========== الإحصائيات ==========
        $stats = [
            'completed' => Donation::where('status', 'completed')->count(),
            'pending'   => Donation::where('status', 'pending')->count(),
            'canceled'  => Donation::where('status', 'canceled')->count(),
        ];

        // ========== عرض التبرعات ==========
        if (Auth::user()->role->name === 'admin') {

            // الأدمن يشوف كل التبرعات
            $donations = Donation::with(['donor', 'hospital', 'request'])
                ->latest()
                ->get();

        } else {

            // المستشفى يشوف فقط تبرعاته
            $hospital = Auth::user()->hospital;

            $donations = Donation::with(['donor', 'request'])
                ->where('hospital_id', $hospital->id)
                ->latest()
                ->get();
        }

        return view('admin.donations.index', compact('donations', 'stats'));
    }

    /**
     * 📌 عرض تفاصيل التبرع داخل Modal
     */
    public function show($id)
    {
        $donation = Donation::with(['donor', 'hospital', 'request'])
            ->findOrFail($id);

        return view('admin.donations.show', compact('donation'));
    }

    /**
     * 📌 تحديث حالة التبرع من لوحة التحكم
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:willing,pending,completed,canceled',
            'units_donated' => 'nullable|integer|min:1',
        ]);

        $donation = Donation::findOrFail($id);

        // تحديث الحالة
        $donation->status = $request->status;

        if ($request->status === 'completed') {
            $donation->units_donated = $request->units_donated ?? 1;
            $donation->donated_at = now();
        }

        $donation->save();

        return back()->with('success', 'تم تحديث حالة التبرع بنجاح ✔');
    }

    /**
     * 📌 موافقة المتبرع عبر API
     */
    public function acceptDonation(Request $request, $requestId)
    {
        $req = BloodRequest::findOrFail($requestId);

        Donation::create([
            'donor_id'      => Auth::id(),
            'hospital_id'   => $req->hospital_id,
            'request_id'    => $req->id,
            'blood_type'    => $req->blood_type,
            'status'        => 'willing',
            'units_donated' => 1,
            'accepted_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل موافقتك على التبرع'
        ]);
    }

    /**
     * 📌 حذف التبرع
     */
    public function destroy($id)
    {
        $donation = Donation::findOrFail($id);
        $donation->delete();

        return back()->with('success', 'تم حذف التبرع بنجاح');
    }
}
