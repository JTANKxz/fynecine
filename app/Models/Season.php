<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = [
        'series_id',
        'season_number',
        'tmdb_id',
        'status'
    ];

    public function series()
    {
        return $this->belongsTo(Serie::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}