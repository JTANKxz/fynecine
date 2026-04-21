<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultHomeSectionItem extends Model
{
    protected $fillable = ['adult_home_section_id', 'item_type', 'item_id', 'order'];

    public function section()
    {
        return $this->belongsTo(AdultHomeSection::class, 'adult_home_section_id');
    }

    /**
     * Get the target model instance
     */
    public function getTargetAttribute()
    {
        if ($this->item_type === 'gallery') {
            return AdultGallery::find($this->item_id);
        } elseif ($this->item_type === 'media') {
            return AdultMedia::find($this->item_id);
        } elseif ($this->item_type === 'model') {
            return AdultModel::find($this->item_id);
        } elseif ($this->item_type === 'collection') {
            return AdultCollection::find($this->item_id);
        }
        return null;
    }
}
