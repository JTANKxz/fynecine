<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortInteraction extends Model
{
    protected $fillable = ['short_id', 'user_id', 'profile_id', 'type', 'watch_seconds', 'metadata'];
    protected $casts = ['metadata' => 'array'];
}
