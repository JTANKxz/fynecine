<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoviePlayLink extends Model
{
    protected $fillable = [
        'movie_id',
        'name',
        'quality',
        'order',
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
    
    protected $casts = [
        'order' => 'integer',
    ];

    // relacionamento com filme
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}