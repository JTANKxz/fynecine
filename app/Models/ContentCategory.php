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
