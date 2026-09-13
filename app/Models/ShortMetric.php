<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortMetric extends Model
{
    protected $fillable = [
        'short_id', 'views', 'unique_viewers', 'likes', 'skips', 'replays', 'completions',
        'total_watch_seconds', 'average_completion', 'engagement_score', 'last_engagement_at',
    ];

    protected $casts = ['last_engagement_at' => 'datetime'];

    public function short(): BelongsTo
    {
        return $this->belongsTo(Short::class);
    }
}
