<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upcoming extends Model
{
    protected $fillable = [
        'tmdb_id',
        'title',
        'type',
        'poster_path',
        'backdrop_path',
        'release_date',
        'trailer_key',
    ];
}
