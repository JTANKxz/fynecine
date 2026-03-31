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
        $events = Event::visible()->orderBy('start_time')->get();
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
            $hasPlan = $user && $user->hasPlan();
            
            foreach ($event->links as $link) {
                $playLinks->push([
                    'id' => $link->id,
                    'name' => $link->name,
                    'url' => ($hasPlan || $link->player_sub === 'free') ? $link->url : null,
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
