<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    protected $fillable = [
        'user_id',
        'content_id',
        'content_type',
        'ip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
