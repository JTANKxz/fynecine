<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'plan_type',
        'plan_category',
        'price',
        'original_price',
        'first_time_discount',
        'duration_days',
        'features',
        'is_active',
        'is_popular',
        'offer_price',
        'offer_expires_at',
        'discount_label',
        'points_cost',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'first_time_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'features' => 'array',
        'points_cost' => 'integer',
        'offer_expires_at' => 'datetime',
    ];
}
