<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'hospital_id',
        'request_id',
        'date_time',
        'status',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    /* العلاقات */

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function request()
    {
        return $this->belongsTo(BloodRequest::class, 'request_id');
    }

    /* Scopes */

    public function scopeUpcoming($query)
    {
        return $query->where('date_time', '>=', now());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
