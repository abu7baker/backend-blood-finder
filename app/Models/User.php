<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'age',
        'gender',
        'city',
        'blood_type',
        'chronic_disease',
        'emergency_phone',
        'password',
        'donation_eligibility',
        'role_id',
        'is_verified',

        // 🔐 Email OTP
        'email_verification_code',
        'email_verification_expires_at',

        // Social / FCM
        'google_id',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',

        // 🔒 حماية رمز التحقق
        'email_verification_code',
    ];

    protected $casts = [
        'email_verified_at'              => 'datetime',
        'email_verification_expires_at'  => 'datetime',
        'last_donation_date'             => 'date',
        // 'password' => 'hashed',
    ];

    /* ========== العلاقات ========== */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hospital()
    {
        return $this->hasOne(Hospital::class, 'user_id');
    }

    // الطلبات التي أنشأها المستخدم (كمريض)
    public function bloodRequests()
    {
        return $this->hasMany(BloodRequest::class, 'requester_id');
    }

    // تبرعات المستخدم (كمتبرع)
    public function donations()
    {
        return $this->hasMany(Donation::class, 'donor_id');
    }

    // مواعيد التبرع
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'donor_id');
    }

    // الإشعارات
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // تغييرات حالة الطلبات
    public function changedRequestStatuses()
    {
        return $this->hasMany(RequestStatusHistory::class, 'changed_by');
    }

    /* ========== Scopes ========== */

    public function scopeDonors($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', 'donor'));
    }

    public function scopePatients($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', 'patient'));
    }

    public function scopeEligibleDonors($query)
    {
        return $query
            ->donors()
            ->where('donation_eligibility', 'eligible');
    }

    public function scopeByBloodType($query, string $bloodType)
    {
        return $query->where('blood_type', $bloodType);
    }
}
