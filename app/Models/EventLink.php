<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventLink extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'url',
        'type',
        'player_sub',
        'link_path',
        'expiration_hours',
        'user_agent',
        'referer',
        'origin',
        'cookie',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }}
