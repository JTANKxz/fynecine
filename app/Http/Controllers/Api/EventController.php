<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Lista eventos que estão "Ao Vivo" ou "Em Breve" (próximos 30min)
     */
    public function index()
    {
        $now = now()->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s');

        $events = Event::with(['homeTeam', 'awayTeam'])
            ->visible()
            ->orderByRaw("CASE WHEN ? >= start_time AND ? <= end_time THEN 0 ELSE 1 END ASC", [$now, $now])
            ->orderBy('start_time')
            ->get()
            ->map(function ($event) {
                $user = auth('sanctum')->user();
                $event->home_team_image = $event->homeTeam?->image_url;
                $event->away_team_image = $event->awayTeam?->image_url;
                $event->is_locked = !($user && $user->canWatchEvents());
                unset($event->homeTeam, $event->awayTeam);
                return $event;
            });

        return response()->json($events);
    }

    /**
     * Detalhes do evento e links (semelhante ao TvChannel)
     */
    public function show($id)
    {
        $event = Event::with('links')->findOrFail($id);
        $config = \App\Models\AppConfig::getSettings();
        $user = Auth::guard('sanctum')->user();

        $playLinks = collect();

        // Se o modo de segurança não estiver bloqueando
        if (!$config->security_mode) {
            $canWatch = $user && $user->canWatchEvents();
            
            foreach ($event->links as $link) {
                $playLinks->push([
                    'id' => $link->id,
                    'name' => $link->name,
                    'url' => ($canWatch || $link->player_sub === 'free') ? $link->url : null,
                    'type' => $link->type
                ]);
            }
        }

        $event->play_links = $playLinks->values();
        // Remove relationships from the root to keep it clean
        unset($event->links);

        return response()->json($event);
    }
}
