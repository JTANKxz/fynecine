<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_url',
        'big_picture_url',
        'action_type',
        'action_value',
        'is_global',
        'user_id',
        'expires_at',
        'push_status',
        'segment',
        'is_in_app',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'is_in_app' => 'boolean',
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
    }

    public function scopeInApp($query)
    {
        return $query->where('is_in_app', true);
    }

    public function scopeByPush($query)
    {
        return $query->whereNotNull('push_status')->where('push_status', '!=', 'none');
    }
}
