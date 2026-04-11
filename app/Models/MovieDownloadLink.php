<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieDownloadLink extends Model
{
    protected $fillable = [
        'movie_id',
        'name',
        'quality',
        'size',
        'url',
        'type',
        'link_path',
        'expiration_hours',
        'download_sub',
        'order',
    ];

    protected $casts = [
        'expiration_hours' => 'integer',
        'order'            => 'integer',
    ];
}
