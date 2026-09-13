<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Championship extends Model
{
    protected $fillable = [
        'name', 'external_provider', 'external_id', 'sport_id', 'country_id',
        'is_sports_enabled', 'auto_sync', 'display_order', 'last_synced_at',
    ];

    protected $casts = [
        'is_sports_enabled' => 'boolean',
        'auto_sync' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
