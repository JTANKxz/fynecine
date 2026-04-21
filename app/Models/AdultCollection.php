<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdultCollection extends Model
{
    protected $fillable = [
        'title', 'slug', 'cover_url', 'description', 'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($collection) {
            if (empty($collection->slug)) {
                $collection->slug = Str::slug($collection->title) . '-' . Str::random(5);
            }
        });
    }

    public function galleries()
    {
        return $this->hasMany(AdultGallery::class, 'adult_collection_id');
    }
}
