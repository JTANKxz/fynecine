<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TvChannelLink extends Model
{
    protected $fillable = [
        'tv_channel_id',
        'name',
        'url',
        'type',
        'order',
        'player_sub',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function channel()
    {
        return $this->belongsTo(TvChannel::class, 'tv_channel_id');
    }
}
