<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'slug'
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

    public function series()
    {
        return $this->belongsToMany(Serie::class, 'genre_series', 'genre_id', 'series_id');
    }
}