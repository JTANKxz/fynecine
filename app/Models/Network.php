<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Network extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_url',
    ];

    protected static function booted()
    {
        static::creating(function ($network) {
            if (empty($network->slug)) {
                $network->slug = Str::slug($network->name);
            }
        });
    }

    public function movies()
    {
        return Movie::whereIn('id',
            \DB::table('network_content')
                ->where('network_id', $this->id)
                ->where('content_type', 'movie')
                ->pluck('content_id')
        )->get();
    }

    public function series()
    {
        return Serie::whereIn('id',
            \DB::table('network_content')
                ->where('network_id', $this->id)
                ->where('content_type', 'series')
                ->pluck('content_id')
        )->get();
    }
}
