<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixPayment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'mp_payment_id',
        'amount',
        'status',
        'pix_qr_code',
        'pix_qr_code_base64',
        'pix_ticket_url',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === 'pending';
    }
}
