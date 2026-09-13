<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'external_provider',
        'external_id',
    ];

    public function homeEvents()
    {
        return $this->hasMany(Event::class, 'home_team_id');
    }

    public function awayEvents()
    {
        return $this->hasMany(Event::class, 'away_team_id');
    }
}
