<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HospitalController extends Controller
{
    use LogsActivity;

    /**
     * عرض قائمة المستشفيات مع الإحصائيات
     */
    public function index(Request $request)
    {
        $city   = $request->city;
        $status = $request->status;

        $hospitals = Hospital::with('user')
            ->when($city && $city !== 'all', fn ($q) => $q->where('city', $city))
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض قائمة المستشفيات'
        );

        return view('admin.hospitals.index', [
            'hospitals' => $hospitals,
            'total'     => Hospital::count(),
            'active'    => Hospital::where('status', 'verified')->count(),
            'pending'   => Hospital::where('status', 'pending')->count(),
            'blocked'   => Hospital::where('status', 'blocked')->count(),
        ]);
    }

    /**
     * إرجاع بيانات مستشفى واحد JSON (للمودالات)
     */
    public function json($id)
    {
        $hospital = Hospital::with('user')->findOrFail($id);

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض بيانات المستشفى: ' . $hospital->name
        );

        return response()->json($hospital);
    }

    /**
     * إضافة مستشفى جديد + إنشاء مستخدم للمستشفى
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_name' => 'required|min:3',
            'city'          => 'required',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|unique:users,phone',
            'location'      => 'nullable|string',
            'status'        => 'required|in:verified,pending,blocked',
        ]);

        // إنشاء مستخدم المستشفى
        $user = User::create([
            'full_name'            => $request->hospital_name . ' - إدارة المستشفى',
            'email'                => $request->email,
            'phone'                => $request->phone,
            'city'                 => $request->city,
            'password'             => Hash::make('123456'),
            'role_id'              => 2,
            'donation_eligibility' => 'not_eligible',
            'status'               => 'active',
        ]);

        // إنشاء المستشفى
        $hospital = Hospital::create([
            'user_id'  => $user->id,
            'name'     => $request->hospital_name,
            'city'     => $request->city,
            'location' => $request->location,
            'status'   => $request->status,
        ]);

        // 📝 سجل نشاط
        $this->logActivity(
            'create',
            'إضافة مستشفى جديد: ' . $hospital->name .
            ' (الحالة: ' . $this->hospitalStatusLabel($hospital->status) . ')'
        );

        return back()->with('success', 'تم إضافة المستشفى بنجاح ✔️');
    }

    /**
     * تعديل بيانات المستشفى + المستخدم المرتبط
     */
    public function update(Request $request, $id)
    {
        $hospital = Hospital::with('user')->findOrFail($id);

        // القيم القديمة
        $oldPhone  = $hospital->user->phone;
        $oldEmail  = $hospital->user->email;
        $oldCity   = $hospital->city;
        $oldStatus = $hospital->status;

        $request->validate([
            'hospital_name' => 'required|min:3',
            'city'          => 'required',
            'email'         => 'required|email|unique:users,email,' . $hospital->user_id,
            'phone'         => 'required|unique:users,phone,' . $hospital->user_id,
            'location'      => 'nullable|string',
            'status'        => 'required|in:verified,pending,blocked',
        ]);

        // تحديث المستخدم
        $hospital->user->update([
            'full_name' => $request->hospital_name . ' - إدارة المستشفى',
            'email'     => $request->email,
            'phone'     => $request->phone,
            'city'      => $request->city,
        ]);

        // تحديث المستشفى
        $hospital->update([
            'name'     => $request->hospital_name,
            'city'     => $request->city,
            'location' => $request->location,
            'status'   => $request->status,
        ]);

        // 🧠 تحديد التغييرات
        $changes = [];

        if ($oldPhone !== $hospital->user->phone) {
            $changes[] = 'رقم الهاتف: ' . $oldPhone . ' → ' . $hospital->user->phone;
        }

        if ($oldEmail !== $hospital->user->email) {
            $changes[] = 'البريد الإلكتروني: ' . $oldEmail . ' → ' . $hospital->user->email;
        }

        if ($oldCity !== $hospital->city) {
            $changes[] = 'المدينة: ' . $oldCity . ' → ' . $hospital->city;
        }

        if ($oldStatus !== $hospital->status) {
            $changes[] = 'الحالة: ' .
                $this->hospitalStatusLabel($oldStatus) .
                ' → ' .
                $this->hospitalStatusLabel($hospital->status);
        }

        if (!empty($changes)) {
            $this->logActivity(
                'update',
                'تحديث بيانات المستشفى: ' . $hospital->name . '<br>' .
                implode('<br>', $changes)
            );
        }

        return back()->with('success', 'تم تحديث بيانات المستشفى بنجاح ✔️');
    }

    /**
     * حذف المستشفى + المستخدم المرتبط
     */
    public function destroy($id)
    {
        $hospital = Hospital::with('user')->findOrFail($id);
        $hospitalName = $hospital->name;

        $hospital->delete();

        if ($hospital->user) {
            $hospital->user->delete();
        }

        // 📝 سجل نشاط
        $this->logActivity(
            'delete',
            'حذف المستشفى: ' . $hospitalName
        );

        return back()->with('success', 'تم حذف المستشفى بنجاح ✔️');
    }
}
