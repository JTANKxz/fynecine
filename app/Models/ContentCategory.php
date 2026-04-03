<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'order',
        'is_active',
        'is_nav_visible'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_nav_visible' => 'boolean',
        'order' => 'integer'
    ];
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function movies()
    {
        return $this->hasMany(Movie::class);
    }

    public function series()
    {
        return $this->hasMany(Serie::class);
    }

    public function sliders()
    {
        return $this->hasMany(Slider::class);
    }

    public function homeSections()
    {
        return $this->hasMany(HomeSection::class);
    }
}
