<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cast extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'slug',
        'profile_path',
        'biography',
        'birthday'
    ];

    public function movies()
    {
        return $this->morphedByMany(Movie::class, 'castable')
            ->withPivot('character', 'order');
    }

    public function series()
    {
        return $this->morphedByMany(Serie::class, 'castable')
            ->withPivot('character', 'order');
    }
}