<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes جاهزة للاستخدام
    |--------------------------------------------------------------------------
    */

    // 📌 الإشعارات غير المقروءة
    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }

    // 📌 إشعارات نوع معين
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // 📌 أحدث الإشعارات أولاً
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'DESC');
    }
}
