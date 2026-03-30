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
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }}
