<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultMedia extends Model
{
    protected $fillable = ['adult_gallery_id', 'title', 'url', 'type', 'order'];

    public function gallery()
    {
        return $this->belongsTo(AdultGallery::class, 'adult_gallery_id');
    }
}
