<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use LogsActivity;

    /**
     * تحديث الدور
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // 🔐 حماية: لا يمكن تعديل دور مدير النظام
        if ($role->name === 'admin') {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن تعديل دور مدير النظام');
        }

        $request->validate([
            'name'        => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        // القيم القديمة
        $oldName        = $role->name;
        $oldDescription = $role->description;

        // التحديث
        $role->update([
            'name'        => strtolower($request->name),
            'description' => $request->description,
        ]);

        // بناء وصف التغييرات
        $changes = [];

        if ($oldName !== $role->name) {
            $changes[] = 'اسم الدور: ' . $oldName . ' → ' . $role->name;
        }

        if ($oldDescription !== $role->description) {
            $changes[] = 'الوصف تم تحديثه';
        }

        // ✅ تسجيل النشاط (فقط إذا حصل تغيير)
        if (!empty($changes)) {
            $this->logActivity(
                'update',
                'تحديث بيانات دور:<br>' . implode('<br>', $changes)
            );
        }

        return redirect()
            ->route('admin.security.index')
            ->with('success', 'تم تحديث الدور بنجاح');
    }

    /**
     * حذف الدور
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // 🔐 حماية: لا يمكن حذف دور مدير النظام
        if ($role->name === 'admin') {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن حذف دور مدير النظام');
        }

        $roleName = $role->name;

        $role->delete();

        // ✅ تسجيل النشاط
        $this->logActivity(
            'delete',
            'حذف دور من النظام: ' . $roleName
        );

        return redirect()
            ->route('admin.security.index')
            ->with('success', 'تم حذف الدور بنجاح');
    }
}
