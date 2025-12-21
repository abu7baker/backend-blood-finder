<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'title',
        'description',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /* العلاقات */

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /* Scopes */

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
