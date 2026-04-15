<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardClaim extends Model
{
    protected $fillable = [
        'user_id',
        'claimed_date',
    ];

    protected $casts = [
        'claimed_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
