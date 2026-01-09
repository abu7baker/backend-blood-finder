<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected function logActivity(string $action, ?string $description = null, ?int $userId = null): void
    {
        ActivityLog::create([
            'user_id'     => $userId ?? Auth::id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
        ]);
    }

    protected function activityLabel(string $action): string
    {
        return match ($action) {
            'login'        => 'تسجيل دخول',
            'logout'       => 'تسجيل خروج',
            'view'         => 'عرض',
            'create'       => 'إضافة',
            'update'       => 'تعديل',
            'delete'       => 'حذف',
            'status'       => 'تحديث حالة',
            'approve'      => 'موافقة',
            'reject'       => 'رفض',
            'export'       => 'تصدير',
            'login_failed' => 'محاولة فاشلة',
            'session_end'  => 'إنهاء جلسة',
            default        => 'نشاط',
        };
    }

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
            'reject', 'delete', 'login_failed' => 'danger',
            'export'  => 'dark',
            'session_end' => 'secondary',
            default   => 'secondary',
        };
    }

    protected function hospitalStatusLabel(string $status): string
    {
        return match ($status) {
            'verified' => 'موثق',
            'pending'  => 'قيد المراجعة',
            'blocked'  => 'محظور',
            'rejected' => 'مرفوض',
            default    => $status,
        };
    }

    protected function userStatusLabel(string $status): string
    {
        return match ($status) {
            'active'  => 'نشط',
            'blocked' => 'محظور',
            default   => $status,
        };
    }

    protected function roleLabel(string $role): string
    {
        return match ($role) {
            'admin'    => 'مدير النظام',
            'hospital' => 'المستشفى',
            'user'     => 'مستخدم',
            default    => $role,
        };
    }
}
