<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestUser extends Model
{
    protected $fillable = [
        'request_id',
        'user_id',
        'role_in_request', // donor
        'status',          // pending | accepted | rejected
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /* ================= العلاقات ================= */

    public function request()
    {
        return $this->belongsTo(BloodRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
