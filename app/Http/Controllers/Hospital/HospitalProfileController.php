<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HospitalProfileController extends Controller
{
    /**
     * صفحة الملف الشخصي
     */
    public function index()
    {
        $user = Auth::user();
        $hospital = $user->hospital;

        return view('hospital.profile.index', compact('user', 'hospital'));
    }

    /**
     * التحقق الفوري من كلمة المرور الحالية عبر AJAX
     */
    public function checkPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'valid' => false,
                'message' => '❌ كلمة المرور الحالية غير صحيحة'
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => '✔ كلمة المرور الحالية صحيحة'
        ]);
    }

    /**
     * تحديث بيانات المستشفى
     */
    public function updateHospital(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'city'            => 'required|string|max:255',
            'location'        => 'required|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $hospital = $user->hospital;

        $hospital->update([
            'name'     => $request->name,
            'city'     => $request->city,
            'location' => $request->location,
        ]);

        $user->update([
            'emergency_phone' => $request->emergency_phone,
        ]);

        return back()->with('success', 'تم تحديث بيانات المستشفى بنجاح');
    }

    /**
     * تحديث البريد ورقم الهاتف
     */
    public function updateCredentials(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'phone' => "required|string|max:20",
        ]);

        $user->update([
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'تم تحديث بيانات الحساب بنجاح');
    }

    /**
     * تحديث كلمة المرور
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // تحقق أساسي
        $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        // تأكد أن كلمة السر الحالية صحيحة
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => '❌ كلمة المرور الحالية غير صحيحة'
            ]);
        }

        // تأكد من المطابقة
        if ($request->password !== $request->password_confirmation) {
            return back()->withErrors([
                'password_confirmation' => '❌ كلمتا المرور غير متطابقتين'
            ]);
        }

        // تحديث كلمة السر
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}
