<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'home_team',
        'away_team',
        'image_url',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function links()
    {
        return $this->hasMany(EventLink::class);
    }

    /**
     * Retorna o status dinâmico do evento (América/São_Paulo)
     */
    public function getStatusAttribute()
    {
        $now = now()->setTimezone('America/Sao_Paulo');
        $start = \Illuminate\Support\Carbon::parse($this->start_time)->setTimezone('America/Sao_Paulo');
        $end = \Illuminate\Support\Carbon::parse($this->end_time)->setTimezone('America/Sao_Paulo');

        if ($now->gt($end)) {
            return 'Encerrado';
        }

        if ($now->gte($start) && $now->lte($end)) {
            return 'Ao Vivo';
        }

        if ($start->diffInMinutes($now) <= 30) {
            return 'Em Breve';
        }

        return 'Agendado';
    }

    /**
     * Scope para eventos que devem aparecer na listagem (Ativos + (Ao Vivo ou Em Breve))
     */
    public function scopeVisible($query)
    {
        $now = now()->setTimezone('America/Sao_Paulo');
        $soonThreshold = $now->copy()->addMinutes(30);

        return $query->where('is_active', true)
            ->where('start_time', '<=', $soonThreshold)
            ->where('end_time', '>=', $now);
    }}
