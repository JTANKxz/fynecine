<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvatarCategory extends Model
{
    protected $fillable = ['name'];

    public function avatars(): HasMany
    {
        return $this->hasMany(Avatar::class);
    }
}
