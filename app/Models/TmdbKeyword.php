<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmdbKeyword extends Model
{
    protected $fillable = ['tmdb_id', 'name'];

    public function movies()
    {
        return $this->morphedByMany(Movie::class, 'keywordable');
    }

    public function series()
    {
        return $this->morphedByMany(Serie::class, 'keywordable');
    }
}
