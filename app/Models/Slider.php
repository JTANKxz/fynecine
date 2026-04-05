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

    public function getImageUrlAttribute()
    {
        $content = $this->content;
        if (!$content) return null;
        return $content->backdrop_url ?? $content->poster_url ?? null;
    }
}