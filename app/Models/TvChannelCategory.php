<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TvChannelCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function channels()
    {
        return $this->belongsToMany(
            TvChannel::class,
            'tv_channel_category_channel',
            'tv_channel_category_id',
            'tv_channel_id'
        );
    }
}
