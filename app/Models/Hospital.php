<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'city',
        'location',
        'status',
    ];

    // كل مستشفى له مستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bloodRequests()
    {
        return $this->hasMany(BloodRequest::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function bloodStock()
    {
        return $this->hasMany(BloodStock::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /* Scopes */

    public function scopeActive($query)
    {
        return $query->where('status', 'verified');
    }
}
