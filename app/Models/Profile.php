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
        'is_main',
        'is_adult_enabled',
        'adult_pin'
    ];

    protected $casts = [
        'is_kids' => 'boolean',
        'is_main' => 'boolean',
        'is_adult_enabled' => 'boolean',
    ];

    protected $hidden = [
        'pin',
        'adult_pin'
    ];

    protected $appends = [
        'has_pin',
        'avatar_url',
    ];

    public function getHasPinAttribute(): bool
    {
        return !empty($this->pin);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        return asset('storage/' . $this->avatar);
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
