<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_admin',
        'plan_type',
        'plan_expires_at',
        'features',
    ];

    /**
     * The attributes that should be appends for serialization.
     *
     * @var array<string, string>
     */
    protected $appends = [
        'has_plan',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'plan_expires_at' => 'datetime',
            'features' => 'array',
        ];
    }

    public function getHasPlanAttribute()
    {
        return $this->hasPlan();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin; // retorna true ou false
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'user_coupons');
    }

    public function hasPlan(): bool
    {
        if ($this->plan_type === 'free') {
            return false;
        }

        // Se tem data de expiração, confere se ainda é válida
        if ($this->plan_expires_at && $this->plan_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isPremium(): bool
    {
        return $this->hasPlan() && ($this->plan_type === 'premium');
    }

    public function isBasic(): bool
    {
        return $this->hasPlan() && ($this->plan_type === 'basic');
    }

    public function hasFeature(string $feature): bool
    {
        if (!$this->hasPlan() || !is_array($this->features)) {
            return false;
        }

        return in_array($feature, $this->features);
    }

    public function maxProfilesCount(): int
    {
        if ($this->isPremium()) {
            return 5;
        }

        if ($this->isBasic()) {
            return 2;
        }

        return 1; // Free
    }
}
