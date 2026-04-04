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
        'home_team_id',
        'away_team_id',
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

    protected $appends = ['status', 'display_time'];

    public function getDisplayTimeAttribute()
    {
        $now = now()->setTimezone('America/Sao_Paulo');
        $start = \Illuminate\Support\Carbon::parse($this->start_time)->setTimezone('America/Sao_Paulo');
        
        $time = $start->format('H:i');
        
        if ($start->isSameDay($now)) {
            return "Hoje • {$time}";
        } elseif ($start->isSameDay($now->copy()->addDay())) {
            return "Amanhã • {$time}";
        } else {
            return $start->format('d/m') . " • {$time}";
        }
    }

    public function links()
    {
        return $this->hasMany(EventLink::class);
    }

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
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

        return $query->where('is_active', true)
            ->where('end_time', '>=', $now);
    }}
