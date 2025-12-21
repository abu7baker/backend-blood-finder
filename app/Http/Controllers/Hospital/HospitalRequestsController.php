<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\BloodStock;
use App\Models\Notification;
use Illuminate\Http\Request;

class HospitalRequestsController extends Controller
{
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
            'critical'  => BloodRequest::where('hospital_id', $hospital->id)
                                ->where('priority', 'critical')->count(),
            'pending'   => BloodRequest::where('hospital_id', $hospital->id)
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
       تحديث حالة الطلب
    ===================================================== */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);

        $bloodRequest = BloodRequest::with('requester')->findOrFail($id);

        $this->authorizeHospital($bloodRequest);

        $bloodRequest->update([
            'status' => $request->status
        ]);

        // إشعار لمقدم الطلب (المستخدم أو المستشفى)
        Notification::create([
            'user_id' => $bloodRequest->requester_id,
            'title'   => 'تحديث حالة طلب الدم',
            'body'    => 'تم تحديث حالة طلبك إلى: ' . $request->status,
            'type'    => 'blood_request',
        ]);

        return response()->json([
            'success' => true,
            'request' => $bloodRequest
        ]);
    }

    /* =====================================================
       حفظ بيانات المريض
    ===================================================== */
    public function savePatientInfo(Request $request, $id)
    {
        $bloodRequest = BloodRequest::with('requester')->findOrFail($id);

        $this->authorizeHospital($bloodRequest);

        // الحالة: المريض هو مقدم الطلب
        if ($request->has('use_requester')) {

            $user = $bloodRequest->requester;

            $bloodRequest->update([
                'patient_name'   => $user->full_name,
                'patient_age'    => $user->age,
                'patient_gender' => $user->gender,
            ]);
        }
        // الحالة: مريض آخر
        else {

            $request->validate([
                'patient_name'   => 'required|string|max:255',
                'patient_age'    => 'required|integer|min:0',
                'patient_gender' => 'required|in:male,female,M,F',
                'doctor_name'    => 'nullable|string|max:255',
                'diagnosis'      => 'nullable|string|max:255',
            ]);

            $bloodRequest->update([
                'patient_name'   => $request->patient_name,
                'patient_age'    => $request->patient_age,
                'patient_gender' => $request->patient_gender,
                'doctor_name'    => $request->doctor_name,
                'diagnosis'      => $request->diagnosis,
            ]);
        }

        return redirect()
            ->route('hospital.requests.index')
            ->with('success', 'تم حفظ بيانات المريض بنجاح');
    }

    /* =====================================================
       إنشاء طلب دم من المستشفى
    ===================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'patient_name'    => 'required|string|max:255',
            'patient_age'     => 'required|integer|min:1',
            'patient_gender'  => 'required|in:M,F,male,female',
            'blood_type'      => 'required|in:O+,O-,A+,A-,B+,B-,AB+,AB-',
            'units_requested' => 'required|integer|min:1',
            'priority'        => 'required|in:normal,urgent,critical',
            'diagnosis'       => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        $hospital = auth()->user()->hospital;

        if (!$hospital) {
            abort(403, 'هذا الحساب غير مرتبط بمستشفى');
        }

        /* =========================
           فحص المخزون
        ========================= */
        $stock = BloodStock::where('hospital_id', $hospital->id)
            ->where('blood_type', $request->blood_type)
            ->first();

        if ($stock && $stock->units_available >= $request->units_requested) {

            Notification::create([
                'user_id' => $hospital->user_id,
                'title'   => 'تنبيه مخزون الدم',
                'body'    => "لديك {$stock->units_available} وحدة من فصيلة {$request->blood_type} في المخزون.",
                'type'    => 'stock_alert',
            ]);

            return redirect()
                ->back()
                ->with('error', 'هذه الفصيلة متوفرة في المخزون.');
        }

        /* =========================
           إنشاء الطلب
        ========================= */
        $bloodRequest = BloodRequest::create([
            'requester_id'    => auth()->id(),
            'hospital_id'     => $hospital->id,
            'patient_name'    => $request->patient_name,
            'patient_age'     => $request->patient_age,
            'patient_gender'  => $request->patient_gender,
            'blood_type'      => $request->blood_type,
            'units_requested' => $request->units_requested,
            'priority'        => $request->priority,
            'diagnosis'       => $request->diagnosis,
            'notes'           => $request->notes,
            'status'          => 'pending',
        ]);

        Notification::create([
            'user_id' => $hospital->user_id,
            'title'   => 'تم إنشاء طلب دم',
            'body'    => "تم إنشاء طلب دم للمريض {$bloodRequest->patient_name}",
            'type'    => 'blood_request',
        ]);

        return redirect()
            ->route('hospital.requests.index')
            ->with('success', 'تم إرسال طلب الدم بنجاح');
    }

    /* =====================================================
       حماية الوصول (مشتركة)
    ===================================================== */
    private function authorizeHospital(BloodRequest $request)
    {
        if ($request->hospital_id !== auth()->user()->hospital->id) {
            abort(403);
        }
    }
}
