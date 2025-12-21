<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodStock extends Model
{
    use HasFactory;

    protected $table = 'blood_stock';

    protected $fillable = [
        'hospital_id',
        'blood_type',
        'units_available',
        'units_reserved',
        'units_expired',
    ];

    protected $casts = [
        'units_available' => 'integer',
        'units_reserved'  => 'integer',
        'units_expired'   => 'integer',
    ];

    /* العلاقات */

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /* Scopes */

    public function scopeByBloodType($query, string $bloodType)
    {
        return $query->where('blood_type', $bloodType);
    }
}
