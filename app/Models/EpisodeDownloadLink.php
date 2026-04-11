<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpisodeDownloadLink extends Model
{
    protected $fillable = [
        'episode_id',
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
