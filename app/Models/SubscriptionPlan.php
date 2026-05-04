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
        'points_cost',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'first_time_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
        'points_cost' => 'integer',
    ];
}
