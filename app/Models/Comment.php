<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'profile_id',
        'commentable_id',
        'commentable_type',
        'body',
        'approved'
    ];

    protected $casts = [
        'approved' => 'boolean'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }
}
