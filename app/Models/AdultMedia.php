<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultMedia extends Model
{
    protected $fillable = [
        'adult_gallery_id', 'adult_model_id', 'adult_category_id', 
        'title', 'url', 'type', 'player_type', 'thumbnail', 'is_active', 'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function gallery()
    {
        return $this->belongsTo(AdultGallery::class, 'adult_gallery_id');
    }

    public function model()
    {
        return $this->belongsTo(AdultModel::class, 'adult_model_id');
    }

    public function category()
    {
        return $this->belongsTo(AdultCategory::class, 'adult_category_id');
    }
}
