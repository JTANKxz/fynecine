<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultModel extends Model
{
    protected $fillable = ['name', 'slug', 'biography', 'photo_url', 'cover_url', 'instagram', 'twitter', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function galleries()
    {
        return $this->hasMany(AdultGallery::class, 'adult_model_id');
    }
}
