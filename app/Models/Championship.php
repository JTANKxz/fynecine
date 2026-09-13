<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Championship extends Model
{
    protected $fillable = [
        'name', 'external_provider', 'external_id', 'sport_id', 'country_id',
        'is_sports_enabled', 'auto_sync', 'display_order', 'last_synced_at',
        'current_season_num', 'current_season_name', 'current_stage_num', 'current_stage_name',
        'stage_type', 'has_standings', 'has_live_standings', 'has_current_stage_standings',
        'has_brackets', 'has_stats', 'provider_color', 'image_url',
    ];

    protected $casts = [
        'is_sports_enabled' => 'boolean',
        'auto_sync' => 'boolean',
        'last_synced_at' => 'datetime',
        'has_standings' => 'boolean',
        'has_live_standings' => 'boolean',
        'has_current_stage_standings' => 'boolean',
        'has_brackets' => 'boolean',
        'has_stats' => 'boolean',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
