<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_request_id',
        'user_id',
        'role_in_request',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /* =====================
       العلاقات
    ===================== */

    // الطلب المرتبط
    public function bloodRequest()
    {
        return $this->belongsTo(BloodRequest::class);
    }

    // المستخدم (المتبرع)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =====================
       Scopes (اختياري لكنها مفيدة)
    ===================== */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}
