<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedDevice extends Model
{
    protected $fillable = [
        'ip_address',
        'device_id',
        'ban_reason'
    ];
}
