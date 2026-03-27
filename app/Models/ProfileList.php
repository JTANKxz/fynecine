<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProfileList extends Model
{
    protected $fillable = [
        'profile_id',
        'listable_id',
        'listable_type',
    ];

    /**
     * Relação polimórfica — aponta para Movie ou Serie.
     */
    public function listable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Perfil dono da lista.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
