<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'tmdb_id',
        'type',
        'title',
        'year',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
