<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedDevice extends Model
{
    protected $fillable = [
        'ip_address',
        'device_id',
        'device_uuid',
        'ban_reason',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}
