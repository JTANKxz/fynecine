<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Championship extends Model
{
    protected $fillable = ['name'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
