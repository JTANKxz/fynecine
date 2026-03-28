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
        'download_sub',
        'order',
    ];
}
