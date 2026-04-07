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
        'content_type',
        'age_rating',
        'content_category_id',
        'tag_text',
        'tag_expires_at'
    ];

    protected $casts = [
        'tag_expires_at' => 'datetime',
    ];

    public function contentCategory()
    {
        return $this->belongsTo(ContentCategory::class);
    }
    
    protected $appends = ['type', 'api_tag_text'];

    public function getTypeAttribute()
    {
        return 'movie';
    }

    public function getApiTagTextAttribute()
    {
        if ($this->tag_expires_at && $this->tag_expires_at->isPast()) {
            return null;
        }
        return $this->tag_text;
    }

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
        static::addGlobalScope(new \App\Models\Scopes\KidsFilterScope);

        static::deleting(function ($movie) {
            \App\Models\Slider::where('content_id', $movie->id)
                ->where('content_type', 'movie')
                ->delete();
        });
    }

    public function downloadLinks()
    {
        return $this->hasMany(MovieDownloadLink::class)->orderBy('order');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }
}