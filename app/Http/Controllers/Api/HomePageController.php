<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomePageController extends Controller
{
    /**
     * 🔥 API الرئيسي لصفحة الهوم
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // ---------------------------
        // 1) إحصائيات المستخدم
        // ---------------------------

        $totalDonations = $user->donations()->count();

        $activeRequests = $user->bloodRequests()
            ->where('status', 'pending')
            ->count();

        $livesSaved = $totalDonations * 3; // كل تبرع = إنقاذ 3 أشخاص (مثال منطقي)

        // ---------------------------
        // 2) آخر التبرعات
        // ---------------------------

        $lastDonations = $user->donations()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'date' => $d->created_at->format('Y-m-d'),
                    'location' => $d->location ?? 'غير محدد',
                ];
            });

        // ---------------------------
        // 3) آخر طلبات الدم
        // ---------------------------

        $lastRequests = $user->bloodRequests()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($r) {
                return [
                    'id'     => $r->id,
                    'blood'  => $r->blood_type,
                    'status' => $r->status,
                    'date'   => $r->created_at->format('Y-m-d'),
                ];
            });

        // ---------------------------
        // 4) الحملات (يمكن لاحقاً جلبها من جدول campaigns)
        // ---------------------------

        $campaigns = [
            [
                "title"       => "حملة إنقاذ الأرواح",
                "description" => "شارك في أكبر حملة تبرع بالدم",
                "image"       => "https://via.placeholder.com/400x200",
            ],
        ];

        // ---------------------------
        // 5) حالة التبرع (متاح / غير متاح)
        // ---------------------------

        $isAvailable = $user->donation_eligibility === 'eligible';

        // ---------------------------
        // 6) JSON جاهز للفلاتر
        // ---------------------------

        return response()->json([
            'success' => true,

            // بيانات المستخدم
            'user' => [
                'id'              => $user->id,
                'full_name'       => $user->full_name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'city'            => $user->city,
                'blood_type'      => $user->blood_type,
                'age'             => $user->age,
                'chronic_disease' => $user->chronic_disease,
                'emergency_phone' => $user->emergency_phone,
                'gender'          => $user->gender,
                'donation_eligibility' => $user->donation_eligibility,
            ],

            // الإحصائيات
            'stats' => [
                'total_donations' => $totalDonations,
                'active_requests' => $activeRequests,
                'lives_saved'     => $livesSaved,
            ],


            // آخر التبرعات
            'last_donations' => $lastDonations,

            // آخر الطلبات
            'last_requests' => $lastRequests,

            // متاح للتبرع
            'available_for_donation' => $isAvailable,

        ], 200);
    }


    /**
     * 🔥 تحديث حالة "متاح للتبرع"
     */
    public function toggleDonation(Request $request)
{
    $user = $request->user();

    $eligible = $request->eligible == "1" ? "eligible" : "not_eligible";

    $user->donation_eligibility = $eligible;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث حالة التبرع',
        'donation_status' => $eligible
    ], 200);
}

}
