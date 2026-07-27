<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upcoming extends Model
{
    protected $appends = ['slug'];

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'linked_serie_id');
    }

    public function getSlugAttribute(): ?string
    {
        return $this->serie?->slug;
    }
    protected $fillable = [
        'tmdb_id',
        'external_key',
        'linked_serie_id',
        'season_number',
        'season_name',
        'title',
        'type',
        'poster_path',
        'backdrop_path',
        'release_date',
        'trailer_key',
    ];
}
