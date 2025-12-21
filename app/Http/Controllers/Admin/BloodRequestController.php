<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\RequestStatusHistory;
use Illuminate\Http\Request;

class BloodRequestController extends Controller
{
    /**
     * عرض قائمة الطلبات + الفلاتر + الإحصائيات
     */
    public function index(Request $request)
    {
        // الإحصائيات
        $stats = [
            'critical' => BloodRequest::where('priority', 'critical')->count(),
            'pending' => BloodRequest::where('status', 'pending')->count(),
            'completed' => BloodRequest::where('status', 'completed')->count(),
        ];

        // فلترة
        $query = BloodRequest::with(['requester', 'hospital'])->latest();

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->priority && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        $requests = $query->paginate(20);
        $hospitals = Hospital::all();

        return view('admin.requests.index', compact('requests', 'stats', 'hospitals'));
    }

    /**
     * إنشاء طلب جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_name' => 'required',
            'patient_age' => 'required|integer|min:1',
            'patient_gender' => 'required',
            'hospital_id' => 'required|exists:hospitals,id',
            'blood_type' => 'required',
            'units_requested' => 'required|integer|min:1',
            'priority' => 'required',
        ]);

        BloodRequest::create([
            'requester_id' => auth()->id(),
            'hospital_id' => $request->hospital_id,
            'blood_type' => $request->blood_type,
            'units_requested' => $request->units_requested,
            'priority' => $request->priority,
            'notes' => $request->notes,
            'patient_name' => $request->patient_name,
            'patient_gender' => $request->patient_gender,
            'patient_age' => $request->patient_age,
            'doctor_name' => $request->doctor_name,
            'diagnosis' => $request->diagnosis,
        ]);

        return back()->with('success', 'تم إضافة الطلب بنجاح');
    }


    /**
     * جلب بيانات الطلب كـ JSON
     */
    public function toJson($id)
    {
        $req = BloodRequest::with(['hospital', 'requester'])->findOrFail($id);
        return response()->json($req);
    }

    /**
     * تعديل بيانات الطلب
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'units_requested' => 'required|integer|min:1',
            'priority' => 'required',
            'notes' => 'nullable'
        ]);

        $req = BloodRequest::findOrFail($id);
        $req->update($request->all());

        return back()->with('success', 'تم تحديث الطلب بنجاح');
    }


    /**
     * تغيير حالة الطلب
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);

        $req = BloodRequest::findOrFail($id);

        // حفظ السجل
        RequestStatusHistory::create([
            'request_id' => $req->id,
            'old_status' => $req->status,
            'new_status' => $request->status,
            'changed_by' => auth()->id(), // ← الحل هنا
            'comment' => $request->comment,
        ]);


        $req->status = $request->status;
        $req->save();

        return back()->with('success', 'تم تحديث الحالة بنجاح');
    }


    /**
     * سجل تغييرات الحالة
     */
    public function history($id)
    {
        $history = RequestStatusHistory::where('request_id', $id)
            ->latest()->get();

        return view('admin.requests.history', compact('history'));
    }


    /**
     * حذف الطلب
     */
    public function destroy($id)
    {
        BloodRequest::findOrFail($id)->delete();
        return back()->with('success', 'تم حذف الطلب');
    }
}
