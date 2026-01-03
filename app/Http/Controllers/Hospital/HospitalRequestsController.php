<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\BloodStock;
use App\Models\Notification;
use App\Models\RequestStatusHistory;
use App\Models\RequestUser;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;


class HospitalRequestsController extends Controller
{
    use LogsActivity;
    /* =====================================================
       رسائل واجهة المستشفى (احترافية وموحّدة)
    ===================================================== */
    private array $uiMessages = [
        'request_created' => 'تم إرسال طلب الدم بنجاح.',
        'request_updated' => 'تم تحديث حالة الطلب بنجاح.',
        'request_no_change' => 'لم يتم تغيير حالة الطلب.',
        'request_completed' => 'تم إغلاق الطلب بعد توفير الدم.',
        'request_rejected' => 'تم رفض طلب الدم.',
        'patient_saved' => 'تم حفظ بيانات المريض بنجاح.',
        'stock_available' => 'هذه الفصيلة متوفرة حالياً في مخزون المستشفى.',
    ];

    /* =====================================================
       عرض الطلبات
    ===================================================== */
    public function index()
    {
        $hospital = auth()->user()->hospital;

        if (!$hospital) {
            abort(403, 'هذا الحساب غير مرتبط بأي مستشفى.');
        }

        $stats = [
            'critical' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('priority', 'critical')->count(),
            'pending' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('status', 'pending')->count(),
            'completed' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('status', 'completed')->count(),
        ];

        $requests = BloodRequest::with('requester')
            ->where('hospital_id', $hospital->id)
            ->latest()
            ->get();

        return view('hospital.requests.index', compact('requests', 'stats'));
    }

    /* =====================================================
       عرض الطلب (JSON)
    ===================================================== */
    public function showJson($id)
    {
        $request = BloodRequest::with(['requester', 'hospital'])->findOrFail($id);
        $this->authorizeHospital($request);

        return response()->json($request);
    }

    /* =====================================================
       تحديث حالة الطلب (DB + Notification + FCM)
    ===================================================== */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);

        $bloodRequest = BloodRequest::with(['requester', 'hospital'])->findOrFail($id);
        $this->authorizeHospital($bloodRequest);

        $hospitalName = $bloodRequest->hospital->name ?? 'المستشفى';
        $oldStatus = $bloodRequest->status;

        if ($oldStatus === $request->status) {
            return response()->json([
                'success' => false,
                'message' => $this->uiMessages['request_no_change'],
            ]);
        }

        $bloodRequest->update([
            'status' => $request->status
        ]);

        /* 📝 تسجيل تغيير الحالة */
        RequestStatusHistory::create([
            'request_id' => $bloodRequest->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        /* ========= رسائل المستخدم (مع اسم المستشفى) ========= */
        $messages = [
            'approved' => [
                'title' => 'تمت الموافقة على طلب الدم 🩸',
                'body' => "تمت الموافقة على طلبك من {$hospitalName} وسيتم إشعار المتبرعين المناسبين.",
            ],
            'rejected' => [
                'title' => 'تعذر توفير الدم ❌',
                'body' => "نعتذر، {$hospitalName} لم يتمكن من توفير الدم في الوقت الحالي.",
            ],
            'completed' => [
                'title' => 'تم توفير الدم ❤️',
                'body' => "تم توفير وحدات الدم المطلوبة من {$hospitalName}. نسأل الله لك الشفاء.",
            ],
            'pending' => [
                'title' => 'طلب الدم قيد المراجعة',
                'body' => "طلبك قيد المراجعة حالياً من قبل {$hospitalName}.",
            ],
        ];

        $msg = $messages[$request->status];

        /* ========= إشعار صاحب الطلب ========= */
        Notification::create([
            'user_id' => $bloodRequest->requester_id,
            'title' => $msg['title'],
            'body' => $msg['body'],
            'type' => 'blood_request',
            'is_read' => false,
            'request_id' => $bloodRequest->id,
        ]);

        if ($bloodRequest->requester && $bloodRequest->requester->fcm_token) {
            try {
                FCMService::send(
                    $bloodRequest->requester->fcm_token,
                    $msg['title'],
                    $msg['body'],
                    [
                        'type' => 'blood_request',
                        'request_id' => (string) $bloodRequest->id,
                        'status' => $request->status,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM USER ERROR: ' . $e->getMessage());
            }
        }

        /* ========= إشعار المتبرعين عند الموافقة ========= */
        if ($request->status === 'approved') {
            $this->notifyEligibleDonors($bloodRequest);
        }

        return response()->json([
            'success' => true,
            'message' => $this->uiMessages['request_updated'],
            'request' => $bloodRequest
        ]);
    }

    /* =====================================================
       🧑‍🦰 إشعار المتبرعين (DB + FCM)
       ✅ إصلاح مهم: إنشاء RequestUser قبل إرسال الإشعار
    ===================================================== */
    private function notifyEligibleDonors(BloodRequest $request)
    {
        $hospital = $request->hospital; // علاقة hospital في BloodRequest
        $hospitalName = $hospital->name ?? 'المستشفى';
        $hospitalCity = $hospital->city ?? null;

        $donors = User::eligibleDonors()
            ->where('blood_type', $request->blood_type)
            ->when($hospitalCity, fn($q) => $q->where('city', $hospitalCity))
            ->get();

        logger('DONOR ALERT DEBUG', [
            'request_id' => $request->id,
            'donors_count' => $donors->count(),
            'city' => $hospitalCity,
            'hospital_name' => $hospitalName,
        ]);

        foreach ($donors as $donor) {

            // ✅ منع تكرار نفس المتبرع لنفس الطلب
            $existsPivot = RequestUser::where('blood_request_id', $request->id)
                ->where('user_id', $donor->id)
                ->exists();

            if (!$existsPivot) {
                RequestUser::create([
                    'blood_request_id' => $request->id,
                    'user_id' => $donor->id,
                    'role_in_request' => 'donor',
                    'status' => 'pending',
                ]);
            }

            // 🗂 حفظ الإشعار في DB
            $body = "مستشفى {$hospitalName} يطلب دم لفصيلة {$request->blood_type} في مدينتك. هل تستطيع التبرع؟";

            Notification::create([
                'user_id' => $donor->id,
                'title' => '🩸 يوجد طلب تبرع بالدم',
                'body' => $body,
                'type' => 'blood_request_donor_alert',
                'is_read' => false,
                'request_id' => $request->id,
            ]);

            // 📲 Push Notification
            if ($donor->fcm_token) {
                try {
                    FCMService::send(
                        $donor->fcm_token,
                        '🩸 طلب تبرع بالدم',
                        "مستشفى {$hospitalName} يحتاج دم {$request->blood_type}",
                        [
                            'type' => 'donor_alert',
                            'request_id' => (string) $request->id,
                            'status' => 'approved',
                            'blood_type' => $request->blood_type,
                            'city' => $hospitalCity,
                        ]
                    );
                } catch (\Throwable $e) {
                    logger('FCM DONOR ERROR: ' . $e->getMessage());
                }
            }
        }
    }

    /* =====================================================
       حفظ بيانات المريض
    ===================================================== */
    public function savePatientInfo(Request $request, $id)
    {
        $bloodRequest = BloodRequest::with('requester')->findOrFail($id);
        $this->authorizeHospital($bloodRequest);

        if ($request->has('use_requester')) {
            $user = $bloodRequest->requester;

            $bloodRequest->update([
                'patient_name' => $user->full_name,
                'patient_age' => $user->age,
                'patient_gender' => $user->gender,
            ]);
        } else {
            $request->validate([
                'patient_name' => 'required|string|max:255',
                'patient_age' => 'required|integer|min:0',
                'patient_gender' => 'required|in:male,female,M,F',
                'doctor_name' => 'nullable|string|max:255',
                'diagnosis' => 'nullable|string|max:255',
            ]);

            $bloodRequest->update($request->only([
                'patient_name',
                'patient_age',
                'patient_gender',
                'doctor_name',
                'diagnosis'
            ]));
        }

        return redirect()
            ->route('hospital.requests.index')
            ->with('success', $this->uiMessages['patient_saved']);
    }

    /* =====================================================
       إنشاء طلب دم من المستشفى
    ===================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_age' => 'required|integer|min:1',
            'patient_gender' => 'required|in:M,F,male,female',
            'blood_type' => 'required|in:O+,O-,A+,A-,B+,B-,AB+,AB-',
            'units_requested' => 'required|integer|min:1',
            'priority' => 'required|in:normal,urgent,critical',
            'diagnosis' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $hospital = auth()->user()->hospital;
        if (!$hospital)
            abort(403);

        $stock = BloodStock::where('hospital_id', $hospital->id)
            ->where('blood_type', $request->blood_type)
            ->first();

        if ($stock && $stock->units_available >= $request->units_requested) {
            Notification::create([
                'user_id' => $hospital->user_id,
                'title' => 'تنبيه مخزون الدم',
                'body' => "المخزون يحتوي على {$stock->units_available} وحدة من فصيلة {$request->blood_type}.",
                'type' => 'stock_alert',
            ]);

            return redirect()->back()
                ->with('error', $this->uiMessages['stock_available']);
        }

        BloodRequest::create([
            'requester_id' => auth()->id(),
            'hospital_id' => $hospital->id,
            'patient_name' => $request->patient_name,
            'patient_age' => $request->patient_age,
            'patient_gender' => $request->patient_gender,
            'blood_type' => $request->blood_type,
            'units_requested' => $request->units_requested,
            'priority' => $request->priority,
            'diagnosis' => $request->diagnosis,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        $this->logActivity(
            'create',
            'إنشاء طلب دم جديد (الفصيلة: ' . $request->blood_type .
            ', الكمية: ' . $request->units_requested . ')'
        );

        return redirect()
            ->route('hospital.requests.index')
            ->with('success', $this->uiMessages['request_created']);
    }

    /* =====================================================
       حماية الوصول
    ===================================================== */
    private function authorizeHospital(BloodRequest $request)
    {
        if ($request->hospital_id !== auth()->user()->hospital->id) {
            abort(403);
        }
    }
}
