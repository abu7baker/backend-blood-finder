<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\RequestStatusHistory;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class BloodRequestController extends Controller
{
    use LogsActivity;

    /**
     * عرض قائمة الطلبات + الفلاتر + الإحصائيات
     */
    public function index(Request $request)
    {
        // الإحصائيات
        $stats = [
            'critical'  => BloodRequest::where('priority', 'critical')->count(),
            'pending'   => BloodRequest::where('status', 'pending')->count(),
            'completed' => BloodRequest::where('status', 'completed')->count(),
        ];

        // فلترة
        $query = BloodRequest::with(['requester', 'hospital'])->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->priority && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        $requests  = $query->paginate(20);
        $hospitals = Hospital::all();

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض قائمة طلبات الدم'
        );

        return view('admin.requests.index', compact('requests', 'stats', 'hospitals'));
    }

    /**
     * إنشاء طلب جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_name'   => 'required',
            'patient_age'    => 'required|integer|min:1',
            'patient_gender' => 'required',
            'hospital_id'    => 'required|exists:hospitals,id',
            'blood_type'     => 'required',
            'units_requested'=> 'required|integer|min:1',
            'priority'       => 'required',
        ]);

        $bloodRequest = BloodRequest::create([
            'requester_id'    => auth()->id(),
            'hospital_id'     => $request->hospital_id,
            'blood_type'      => $request->blood_type,
            'units_requested' => $request->units_requested,
            'priority'        => $request->priority,
            'notes'           => $request->notes,
            'patient_name'    => $request->patient_name,
            'patient_gender'  => $request->patient_gender,
            'patient_age'     => $request->patient_age,
            'doctor_name'     => $request->doctor_name,
            'diagnosis'       => $request->diagnosis,
        ]);

        // 📝 سجل نشاط
        $this->logActivity(
            'create',
            'إضافة طلب دم جديد للمريض: ' . $bloodRequest->patient_name .
            ' (الفصيلة: ' . $bloodRequest->blood_type . ')'
        );

        return back()->with('success', 'تم إضافة الطلب بنجاح');
    }

    /**
     * جلب بيانات الطلب كـ JSON
     */
    public function toJson($id)
    {
        $req = BloodRequest::with(['hospital', 'requester'])->findOrFail($id);

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض بيانات طلب دم رقم #' . $req->id
        );

        return response()->json($req);
    }

    /**
     * تعديل بيانات الطلب
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'units_requested' => 'required|integer|min:1',
            'priority'        => 'required',
            'notes'           => 'nullable'
        ]);

        $req = BloodRequest::findOrFail($id);

        // القيم القديمة
        $oldUnits    = $req->units_requested;
        $oldPriority = $req->priority;
        $oldNotes    = $req->notes;

        $req->update($request->all());

        // 🧠 تحديد التغييرات
        $changes = [];

        if ($oldUnits != $req->units_requested) {
            $changes[] = 'عدد الوحدات: ' . $oldUnits . ' → ' . $req->units_requested;
        }

        if ($oldPriority !== $req->priority) {
            $changes[] = 'الأولوية: ' . $oldPriority . ' → ' . $req->priority;
        }

        if ($oldNotes !== $req->notes) {
            $changes[] = 'الملاحظات: تم التعديل';
        }

        if (!empty($changes)) {
            $this->logActivity(
                'update',
                'تحديث بيانات طلب الدم رقم #' . $req->id . '<br>' .
                implode('<br>', $changes)
            );
        }

        return back()->with('success', 'تم تحديث الطلب بنجاح');
    }

    /**
     * تغيير حالة الطلب
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,in_progress,rejected,completed'
        ]);

        $req = BloodRequest::with('hospital')->findOrFail($id);

        $oldStatus = $req->status;
        $newStatus = $request->status;

        // حفظ سجل فني للحالة
        RequestStatusHistory::create([
            'request_id' => $req->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'comment'    => $request->comment,
        ]);

        $req->status = $newStatus;
        $req->save();

        // 📝 سجل نشاط إداري
        $this->logActivity(
            'status',
            'تغيير حالة طلب الدم رقم #' . $req->id .
            ' (المستشفى: ' . $req->hospital->name . ')<br>' .
            'الحالة: ' . $oldStatus . ' → ' . $newStatus
        );

        return back()->with('success', 'تم تحديث الحالة بنجاح');
    }

    /**
     * سجل تغييرات الحالة
     */
    public function history($id)
    {
        $history = RequestStatusHistory::where('request_id', $id)
            ->latest()->get();

        $this->logActivity(
            'view',
            'عرض سجل تغييرات الحالة لطلب الدم رقم #' . $id
        );

        return view('admin.requests.history', compact('history'));
    }

    /**
     * حذف الطلب
     */
    public function destroy($id)
    {
        $req = BloodRequest::findOrFail($id);

        $this->logActivity(
            'delete',
            'حذف طلب دم رقم #' . $req->id .
            ' (المريض: ' . $req->patient_name . ')'
        );

        $req->delete();

        return back()->with('success', 'تم حذف الطلب');
    }
}
