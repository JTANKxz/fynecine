<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNote extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'tasks',
        'color',
        'is_pinned'
    ];

    protected $casts = [
        'tasks' => 'array',
        'is_pinned' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
