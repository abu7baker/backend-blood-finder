<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'hospital_id',
        'blood_type',
        'units_requested',
        'priority',
        'status',
        'notes',
        'patient_name',
        'patient_gender',
        'patient_age',
        'doctor_name',
        'diagnosis',
    ];

    protected $casts = [
        'patient_age' => 'integer',
    ];

    /* العلاقات */


    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'request_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'request_id');
    }

    /* Scopes */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }
}
