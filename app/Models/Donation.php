<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'hospital_id',
        'request_id',
        'blood_type',
        'units_donated',
        'donated_at',
        'status',
        'source'
    ];

    protected $casts = [
        'units_donated' => 'integer',
        'donated_at'    => 'datetime',
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
