<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = [
        'series_id',
        'season_id',
        'episode_number',
        'tmdb_id',
        'name',
        'overview',
        'duration',
        'still_path',
        'status'
    ];

    public function series()
    {
        return $this->belongsTo(Serie::class, 'series_id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function links()
    {
        return $this->hasMany(EpisodeLink::class);
    }
}