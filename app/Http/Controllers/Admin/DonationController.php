<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\BloodRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    use LogsActivity;

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

            $this->logActivity(
                'view',
                'عرض قائمة جميع التبرعات'
            );

        } else {

            // المستشفى يشوف فقط تبرعاته
            $hospital = Auth::user()->hospital;

            $donations = Donation::with(['donor', 'request'])
                ->where('hospital_id', $hospital->id)
                ->latest()
                ->get();

            $this->logActivity(
                'view',
                'عرض قائمة التبرعات الخاصة بالمستشفى: ' . $hospital->name
            );
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

        $this->logActivity(
            'view',
            'عرض تفاصيل التبرع رقم #' . $donation->id
        );

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

        $donation = Donation::with(['donor', 'hospital'])->findOrFail($id);

        // القيم القديمة
        $oldStatus = $donation->status;
        $oldUnits  = $donation->units_donated;

        // تحديث الحالة
        $donation->status = $request->status;

        if ($request->status === 'completed') {
            $donation->units_donated = $request->units_donated ?? 1;
            $donation->donated_at = now();
        }

        $donation->save();

        // 🧠 تحديد التغييرات
        $changes = [];

        if ($oldStatus !== $donation->status) {
            $changes[] = 'الحالة: ' . $oldStatus . ' → ' . $donation->status;
        }

        if ($oldUnits !== $donation->units_donated) {
            $changes[] = 'عدد الوحدات: ' . ($oldUnits ?? 0) . ' → ' . $donation->units_donated;
        }

        if (!empty($changes)) {
            $this->logActivity(
                'update',
                'تحديث حالة التبرع رقم #' . $donation->id . '<br>' .
                implode('<br>', $changes)
            );
        }

        return back()->with('success', 'تم تحديث حالة التبرع بنجاح ✔');
    }

    /**
     * 📌 موافقة المتبرع عبر API
     */
    public function acceptDonation(Request $request, $requestId)
    {
        $req = BloodRequest::with('hospital')->findOrFail($requestId);

        $donation = Donation::create([
            'donor_id'      => Auth::id(),
            'hospital_id'   => $req->hospital_id,
            'request_id'    => $req->id,
            'blood_type'    => $req->blood_type,
            'status'        => 'willing',
            'units_donated' => 1,
            'accepted_at'   => now(),
        ]);

        $this->logActivity(
            'create',
            'موافقة متبرع على طلب دم رقم #' . $req->id .
            ' (المستشفى: ' . $req->hospital->name . ')'
        );

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
        $donation = Donation::with(['donor'])->findOrFail($id);

        $this->logActivity(
            'delete',
            'حذف التبرع رقم #' . $donation->id .
            ' (المتبرع: ' . ($donation->donor->full_name ?? 'غير معروف') . ')'
        );

        $donation->delete();

        return back()->with('success', 'تم حذف التبرع بنجاح');
    }
}
