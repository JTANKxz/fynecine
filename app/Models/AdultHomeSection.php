<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultHomeSection extends Model
{
    protected $fillable = ['title', 'type', 'order', 'is_active', 'limit'];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'limit' => 'integer'
    ];

    public function manualItems()
    {
        return $this->hasMany(AdultHomeSectionItem::class, 'adult_home_section_id')->orderBy('order');
    }
}
