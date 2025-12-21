<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HospitalController extends Controller
{
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

        // 1) إنشاء مستخدم للمستشفى
        $user = User::create([
            'full_name'            => $request->hospital_name . ' - إدارة المستشفى',
            'email'                => $request->email,
            'phone'                => $request->phone,
            'city'                 => $request->city,
            'password'             => Hash::make('123456'),
            'role_id'              => 2, // hospital
            'donation_eligibility' => 'not_eligible',
            'status'               => 'active',
        ]);

        // 2) إنشاء المستشفى وربطه بالمستخدم
        Hospital::create([
            'user_id'  => $user->id,
            'name'     => $request->hospital_name,
            'city'     => $request->city,
            'location' => $request->location,
            'status'   => $request->status,
        ]);

        return back()->with('success', 'تم إضافة المستشفى بنجاح ✔️');
    }

    /**
     * تعديل بيانات المستشفى + المستخدم المرتبط
     */
    public function update(Request $request, $id)
    {
        $hospital = Hospital::with('user')->findOrFail($id);

        $request->validate([
            'hospital_name' => 'required|min:3',
            'city'          => 'required',
            'email'         => 'required|email|unique:users,email,' . $hospital->user_id,
            'phone'         => 'required|unique:users,phone,' . $hospital->user_id,
            'location'      => 'nullable|string',
            'status'        => 'required|in:verified,pending,blocked',
        ]);

        // تحديث حساب المستخدم
        $hospital->user->update([
            'full_name' => $request->hospital_name . ' - إدارة المستشفى',
            'email'     => $request->email,
            'phone'     => $request->phone,
            'city'      => $request->city,
        ]);

        // تحديث بيانات المستشفى
        $hospital->update([
            'name'     => $request->hospital_name,
            'city'     => $request->city,
            'location' => $request->location,
            'status'   => $request->status,
        ]);

        return back()->with('success', 'تم تحديث بيانات المستشفى بنجاح ✔️');
    }

    /**
     * حذف المستشفى + حذف المستخدم المرتبط
     */
    public function destroy($id)
    {
        $hospital = Hospital::with('user')->findOrFail($id);

        // حذف المستشفى ثم المستخدم
        $hospital->delete();
        if ($hospital->user) {
            $hospital->user->delete();
        }

        return back()->with('success', 'تم حذف المستشفى بنجاح ✔️');
    }
}
