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
            if ($user && $user->hasPlan()) {
                // Usuário VIP vê todos os links
                $playLinks = $event->links;
            } else {
                // Usuário sem plano (ou não logado) vê apenas links Free
                $playLinks = $event->links->where('player_sub', 'free');
            }
        }

        $playLinks = collect();

        // Se o modo de segurança não estiver bloqueando
        if (!$config->security_mode) {
            if ($user && $user->hasPlan()) {
                // Usuário VIP vê todos os links
                $playLinks = $event->links;
            } else {
                // Usuário sem plano (ou não logado) vê apenas links Free
                $playLinks = $event->links->where('player_sub', 'free');
            }
        }

        $event->play_links = $playLinks->values()->map(function($link) {
            return [
                'id' => $link->id,
                'name' => $link->name,
                'url' => $link->url,
                'type' => $link->type
            ];
        });

        return response()->json($event);
    }
}
