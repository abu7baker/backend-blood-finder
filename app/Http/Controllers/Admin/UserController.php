<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use LogsActivity;

    /**
     * عرض قائمة المستخدمين
     */
    public function index()
    {
        // الإحصائيات
        $totalUsers   = User::count();
        $activeUsers  = User::where('status', 'active')->count();
        $blockedUsers = User::where('status', 'blocked')->count();

        // جميع المستخدمين (استبعاد المستشفيات)
        $users = User::where('role_id', '!=', 2)
            ->orderBy('id', 'desc')
            ->get();

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض قائمة المستخدمين'
        );

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

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض بيانات المستخدم: ' . $user->full_name
        );

        return view('admin.users.show', compact('user'));
    }

    /**
     * JSON بيانات مستخدم
     */
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

        $role = Role::find($request->role_id);

        $user = User::create([
            'full_name'  => $request->full_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'city'       => $request->city,
            'blood_type' => $request->blood_type,
            'status'     => $request->status ?? 'active',
            'role_id'    => $request->role_id,
            'password'   => Hash::make($request->password),
        ]);

        // 📝 سجل نشاط
        $this->logActivity(
            'create',
            'إضافة مستخدم جديد: ' . $user->full_name .
            ' (الدور: ' . ($role?->name ?? 'غير محدد') . ')'
        );

        return redirect()->back()->with('success', 'تم إضافة المستخدم بنجاح');
    }

    /**
     * صفحة تعديل المستخدم
     */
    public function edit($id)
    {
        $user  = User::findOrFail($id);
        $roles = Role::all();

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'فتح صفحة تعديل المستخدم: ' . $user->full_name
        );

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::with('role')->findOrFail($id);

        // القيم القديمة
        $oldPhone = $user->phone;
        $oldEmail = $user->email;
        $oldCity  = $user->city;
        $oldStatus = $user->status;
        $oldRole  = $user->role?->name;

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

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // 🧠 تحديد التغييرات
        $changes = [];

        if ($oldPhone !== $user->phone) {
            $changes[] = 'رقم الهاتف: ' . $oldPhone . ' → ' . $user->phone;
        }

        if ($oldEmail !== $user->email) {
            $changes[] = 'البريد الإلكتروني: ' . $oldEmail . ' → ' . $user->email;
        }

        if ($oldCity !== $user->city) {
            $changes[] = 'المدينة: ' . $oldCity . ' → ' . $user->city;
        }

        if ($oldStatus !== $user->status) {
            $changes[] = 'الحالة: ' .
                $this->userStatusLabel($oldStatus) .
                ' → ' .
                $this->userStatusLabel($user->status);
        }

        if ($oldRole !== $user->role?->name) {
            $changes[] = 'الدور: ' .
                $this->roleLabel($oldRole) .
                ' → ' .
                $this->roleLabel($user->role?->name);
        }

        if (!empty($changes)) {
            $this->logActivity(
                'update',
                'تحديث بيانات المستخدم: ' . $user->full_name . '<br>' .
                implode('<br>', $changes)
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * حذف المستخدم
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->full_name;

        $user->delete();

        // 📝 سجل نشاط
        $this->logActivity(
            'delete',
            'حذف المستخدم: ' . $userName
        );

        return redirect()->back()->with('success', 'تم حذف المستخدم');
    }

    /**
     * تصدير المستخدمين
     */
    public function export()
    {
        $users = User::with('role')->get();

        // 📝 سجل نشاط
        $this->logActivity(
            'export',
            'تصدير بيانات المستخدمين'
        );

        return response()->json($users);
    }
}
