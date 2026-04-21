<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultGallery extends Model
{
    protected $fillable = [
        'adult_model_id', 'adult_category_id', 'adult_collection_id', 'title', 'slug', 
        'description', 'cover_url', 'type', 'is_active', 'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function collection()
    {
        return $this->belongsTo(AdultCollection::class, 'adult_collection_id');
    }

    public function model()
    {
        return $this->belongsTo(AdultModel::class, 'adult_model_id');
    }

    public function category()
    {
        return $this->belongsTo(AdultCategory::class, 'adult_category_id');
    }

    public function media()
    {
        return $this->hasMany(AdultMedia::class, 'adult_gallery_id')->orderBy('order');
    }
}
