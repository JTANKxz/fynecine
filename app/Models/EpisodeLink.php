<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpisodeLink extends Model
{
    protected $fillable = [
        'episode_id',
        'name',
        'quality',
        'order',
        'url',
        'type',
        'player_sub',
        'skip_intro_start',
        'skip_intro_end',
        'skip_ending_start',
        'skip_ending_end',
        'link_path',
        'expiration_hours',
    ];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public function getSkipIntroStartTimeAttribute()
    {
        return $this->skip_intro_start ? gmdate('i:s', $this->skip_intro_start) : null;
    }
}