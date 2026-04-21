<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'order', 'is_active'];

    public function galleries()
    {
        return $this->hasMany(AdultGallery::class, 'adult_category_id');
    }
}
