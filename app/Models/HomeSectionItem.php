<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSectionItem extends Model
{
    protected $fillable = [
        'home_section_id',
        'content_id',
        'content_type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(HomeSection::class, 'home_section_id');
    }

    public function getContentAttribute()
    {
        if ($this->content_type === 'movie') {
            return Movie::find($this->content_id);
        }
        return Serie::find($this->content_id);
    }
}
