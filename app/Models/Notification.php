<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_url',
        'action_type',
        'action_value',
        'is_global',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }}
