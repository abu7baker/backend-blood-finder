<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * 📝 تسجيل نشاط
     */
    protected function logActivity(string $action, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
        ]);
    }

    /**
     * 🏷️ اسم النشاط بالعربي
     */
    protected function activityLabel(string $action): string
    {
        return match ($action) {
            'login'   => 'تسجيل دخول',
            'logout'  => 'تسجيل خروج',
            'view'    => 'عرض',
            'create'  => 'إضافة',
            'update'  => 'تعديل',
            'delete'  => 'حذف',
            'status'  => 'تغيير حالة',
            'approve' => 'موافقة',
            'reject'  => 'رفض',
            'export'  => 'تصدير',
            default   => 'نشاط',
        };
    }

    /**
     * 🎨 لون النشاط
     */
    protected function activityColor(string $action): string
    {
        return match ($action) {
            'login'   => 'primary',
            'logout'  => 'secondary',
            'view'    => 'info',
            'create'  => 'success',
            'update'  => 'warning',
            'status'  => 'secondary',
            'approve' => 'success',
            'reject', 'delete' => 'danger',
            'export'  => 'dark',
            default   => 'secondary',
        };
    }

    /**
     * 🏥 حالة المستشفى بالعربي
     */
    protected function hospitalStatusLabel(string $status): string
    {
        return match ($status) {
            'verified' => 'موثّق',
            'pending'  => 'قيد المراجعة',
            'blocked'  => 'محظور',
            'rejected' => 'مرفوض',
            default    => $status,
        };
    }

    /**
     * 👤 حالة المستخدم بالعربي
     */
    protected function userStatusLabel(string $status): string
    {
        return match ($status) {
            'active'  => 'نشط',
            'blocked' => 'محظور',
            default   => $status,
        };
    }

    /**
     * 🧑‍💼 اسم الدور
     */
    protected function roleLabel(string $role): string
    {
        return match ($role) {
            'admin'    => 'مدير النظام',
            'hospital' => 'مستشفى',
            'user'     => 'مستخدم',
            default    => $role,
        };
    }
}
