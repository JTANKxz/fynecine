<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'content_id',
        'content_type',
        'position',
        'active',
        'content_category_id'
    ];

    protected $appends = ['title', 'image_url'];

    public function category()
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'content_id');
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'content_id');
    }

    public function getContentAttribute()
    {
        return $this->content_type === 'movie'
            ? $this->movie
            : $this->serie;
    }

    public function getTitleAttribute()
    {
        $content = $this->content;
        if (!$content) return null;
        return $this->content_type === 'movie' ? $content->title : $content->name;
    }

    public function getImageUrlAttribute(): ?string
    {
        $content = $this->content;
        if (!$content) {
            return null;
        }

        // Movie and series imports store the TMDB image path in these fields.
        // `backdrop_url`/`poster_url` do not exist on the models, which made
        // the custom-page slider render without an image.
        $image = $content->backdrop_path ?: $content->poster_path;
        if (!$image) {
            return null;
        }

        return filter_var($image, FILTER_VALIDATE_URL)
            ? $image
            : 'https://image.tmdb.org/t/p/original' . $image;
    }
}