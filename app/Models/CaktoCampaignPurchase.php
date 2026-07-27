<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaktoCampaignPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'cakto_purchase_id',
        'product_id',
        'buyer_name',
        'buyer_email',
        'status',
        'payload',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'approved_at' => 'datetime',
        ];
    }
}
