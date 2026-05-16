<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Avatar extends Model
{
    protected $fillable = ['avatar_category_id', 'image', 'is_default', 'is_kids'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_kids' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AvatarCategory::class, 'avatar_category_id');
    }

    /**
     * Get the full URL for the avatar image
     */
    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
