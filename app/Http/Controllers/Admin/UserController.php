<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**  
     * عرض قائمة المستخدمين
     */
    public function index()
    {
        // الإحصائيات
        $totalUsers   = User::count(); 
        $activeUsers  = User::where('status', 'active')->count();
        $blockedUsers = User::where('status', 'blocked')->count();

        // جميع المستخدمين مع العلاقة Role
       $users = User::where('role_id', '!=', 2)  // استبعاد المستشفيات
                 ->orderBy('id', 'desc')
                 ->get();
        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'blockedUsers'
        ));
    }

    /**  
     * عرض مستخدم معيّن
     */
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }
    
 public function showJson($id)
{
    $user = User::with('role')->findOrFail($id);

    return response()->json([
        'id'         => $user->id,
        'full_name'  => $user->full_name,
        'email'      => $user->email,
        'phone'      => $user->phone,
        'city'       => $user->city,
        'blood_type' => $user->blood_type,
        'status'     => $user->status,
        'role_name'  => $user->role->name ?? null,
        'created_at' => $user->created_at->format('Y-m-d'),
    ]);
}


    /**  
     * حفظ مستخدم جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'phone'     => 'required',
            'password'  => 'required|min:6',
            'role_id'   => 'required',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'city'      => $request->city,
            'blood_type'=> $request->blood_type,
            'status'    => $request->status ?? 'active',
            'role_id'   => $request->role_id,
            'password'  => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'تم إضافة المستخدم بنجاح');
    }

    /**  
     * صفحة تعديل المستخدم
     */
    public function edit($id)
    {
        $user  = User::findOrFail($id);
        $roles = Role::all(); // عشان نعرض أنواع الأدوار في التعديل

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**  
     * تحديث بيانات المستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required',
            'phone'     => 'required',
            'role_id'   => 'required',
        ]);

        $data = [
            'full_name'  => $request->full_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'city'       => $request->city,
            'blood_type' => $request->blood_type,
            'status'     => $request->status,
            'role_id'    => $request->role_id,
        ];

        // إذا عدّل كلمة السر
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
                         ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'تم حذف المستخدم');
    }


    public function export()
    {
        $users = User::with('role')->get();

        return response()->json($users);
    }
}
