<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortInteraction extends Model
{
    protected $fillable = ['short_id', 'user_id', 'profile_id', 'type', 'watch_seconds', 'watch_percentage', 'session_id', 'metadata'];
    protected $casts = ['metadata' => 'array'];

    public function short(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Short::class);
    }
}
