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
        'download_sub',
        'order',
    ];
}
