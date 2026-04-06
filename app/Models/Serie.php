<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $fillable = [

        'tmdb_id',
        'name',
        'slug',
        'first_air_year',
        'last_air_year',
        'number_of_seasons',
        'number_of_episodes',
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

    protected $appends = ['type'];

    public function getTypeAttribute()
    {
        return 'series';
    }



    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_series', 'series_id', 'genre_id');
    }

    public function seasons()
    {
        return $this->hasMany(Season::class, 'series_id');
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
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

        static::deleting(function ($serie) {
            \App\Models\Slider::where('content_id', $serie->id)
                ->where('content_type', 'series')
                ->delete();
        });
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }
}