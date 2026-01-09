<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('admin.profile.index', compact('user'));
    }

    public function checkPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'valid' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة',
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'كلمة المرور الحالية صحيحة',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name'       => 'required|string|max:255',
            'city'            => 'nullable|string|max:255',
            'age'             => 'nullable|string|max:10',
            'gender'          => 'nullable|string|max:20',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        $user->update([
            'full_name'       => $request->full_name,
            'city'            => $request->city,
            'age'             => $request->age,
            'gender'          => $request->gender,
            'emergency_phone' => $request->emergency_phone,
        ]);

        return back()->with('success', 'تم تحديث بيانات الملف الشخصي بنجاح');
    }

    public function updateCredentials(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'phone' => "required|string|max:20|unique:users,phone,{$user->id}",
        ]);

        $user->update([
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'تم تحديث بيانات الحساب بنجاح');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'كلمة المرور الحالية غير صحيحة',
            ]);
        }

        if ($request->password !== $request->password_confirmation) {
            return back()->withErrors([
                'password_confirmation' => 'تأكيد كلمة المرور غير مطابق',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'تم تحديث كلمة المرور بنجاح');
    }
}
