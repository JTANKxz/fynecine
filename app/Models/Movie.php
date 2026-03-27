<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'tmdb_id',
        'imdb_id',
        'title',
        'slug',
        'release_year',
        'runtime',
        'rating',
        'overview',
        'poster_path',
        'backdrop_path',
        'trailer_key',
        'trailer_url',
        'content_type'
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_movie', 'movie_id', 'genre_id');
    }

    public function getTrailerEmbedAttribute()
    {
        if (!$this->trailer_key)
            return null;

        return "https://www.youtube.com/embed/" . $this->trailer_key;
    }

    public function playLinks()
    {
        return $this->hasMany(MoviePlayLink::class)
            ->orderBy('order');
    }

    public function cast()
    {
        return $this->morphToMany(Cast::class, 'castable')
            ->withPivot('character', 'order');
    }

    public function profileLists()
    {
        return $this->morphMany(ProfileList::class, 'listable');
    }
    protected static function booted()
    {
        static::deleting(function ($movie) {
            \App\Models\Slider::where('content_id', $movie->id)
                ->where('content_type', 'movie')
                ->delete();
        });
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }
}