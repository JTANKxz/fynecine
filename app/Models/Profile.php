<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'avatar',
        'is_kids',
        'pin',
        'is_main'
    ];

    protected $casts = [
        'is_kids' => 'boolean',
        'is_main' => 'boolean',
    ];

    protected $hidden = [
        'pin'
    ];

    protected $appends = [
        'has_pin'
    ];

    public function getHasPinAttribute(): bool
    {
        return !empty($this->pin);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(ProfileList::class);
    }
}
