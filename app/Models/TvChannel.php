<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TvChannel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_url',
    ];

    protected static function booted()
    {
        static::creating(function ($channel) {
            if (empty($channel->slug)) {
                $channel->slug = Str::slug($channel->name);
            }
        });
    }

    public function categories()
    {
        return $this->belongsToMany(
            TvChannelCategory::class,
            'tv_channel_category_channel',
            'tv_channel_id',
            'tv_channel_category_id'
        );
    }

    public function links()
    {
        return $this->hasMany(TvChannelLink::class)->orderBy('order');
    }
}
