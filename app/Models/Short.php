<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Short extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'source_provider', 'source_url', 'external_id',
        'playback_kind', 'embed_url', 'thumbnail_url', 'duration_seconds', 'hashtags',
        'category', 'language', 'is_active', 'published_at', 'last_checked_at',
        'availability', 'availability_message', 'provider_metadata', 'related_type', 'related_id', 'related_title',
    ];

    protected $casts = [
        'is_active' => 'boolean', 'published_at' => 'datetime', 'last_checked_at' => 'datetime',
        'provider_metadata' => 'array',
    ];

    public function interactions(): HasMany
    {
        return $this->hasMany(ShortInteraction::class);
    }
}
