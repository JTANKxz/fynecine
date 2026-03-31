<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_token',
        'device_type',
        'app_version',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
