<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'device_uuid',
        'device_name',
        'device_type',
        'ip_address',
        'user_agent',
        'location',
        'last_used_at',
        'expires_at',
    ];
}
