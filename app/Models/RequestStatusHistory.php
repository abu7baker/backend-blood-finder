<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'request_status_history';

    protected $fillable = [
        'request_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /* العلاقات */

    public function request()
    {
        return $this->belongsTo(BloodRequest::class, 'request_id');
    }

   public function changer()
{
    return $this->belongsTo(User::class, 'changed_by');
}

}
