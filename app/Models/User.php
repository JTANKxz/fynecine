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

    protected $appends = [
        'has_plan',
        'max_profiles',
        'plan_status',
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

    public function getMaxProfilesAttribute(): int
    {
        return $this->maxProfilesCount();
    }

    public function getHasPlanAttribute(): bool
    {
        return $this->hasPlan();
    }

    public function getPlanStatusAttribute(): string
    {
        if ($this->plan_type === 'free') {
            return 'none';
        }

        if ($this->plan_expires_at && $this->plan_expires_at->isPast()) {
            return 'expired';
        }

        return 'active';
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
            return 6; // Master + 5
        }

        if ($this->isBasic()) {
            return 3; // Master + 2
        }

        return 1; // Free: Apenas o perfil principal
    }

    /**
     * Limites Diários: Free=1, Basic=3, Premium=5
     */
    public function getDailyRequestLimit(): int
    {
        if ($this->isPremium()) return 5;
        if ($this->isBasic()) return 3;
        return 1;
    }

    public function getDailyTicketLimit(): int
    {
        if ($this->isPremium()) return 5;
        if ($this->isBasic()) return 3;
        return 1;
    }

    /**
     * Acesso a Eventos Ao Vivo (Apenas Premium)
     */
    public function canWatchEvents(): bool
    {
        return $this->isPremium();
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function readNotifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function unreadNotifications()
    {
        $segments = ['all'];
        $status = $this->getPlanStatusAttribute();
        if ($status === 'active') {
            $segments[] = $this->plan_type ?: 'free';
        } elseif ($status === 'expired') {
            $segments[] = 'expired';
        } else {
            $segments[] = 'free';
        }

        // Pega as notificações que o usuário ainda não leu (não estão na tabela pivô)
        return Notification::active()
            ->where('is_in_app', true)
            ->where(function ($q) use ($segments) {
                $q->whereIn('segment', $segments)
                  ->orWhere('user_id', $this->id);
            })
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                      ->from('notification_user')
                      ->whereColumn('notification_user.notification_id', 'notifications.id')
                      ->where('notification_user.user_id', $this->id);
            });
    }

    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }
}
