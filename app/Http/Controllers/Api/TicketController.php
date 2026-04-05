<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Retorna lista de tópicos padrão de suporte.
     */
    public function getTopics(): JsonResponse
    {
        return response()->json([
            [
                'id' => 'movie_issue',
                'name' => 'Problema em Filme/Série',
                'subtopics' => ['Sem áudio', 'Travando', 'Legenda fora de sincronia', 'Qualidade ruim']
            ],
            [
                'id' => 'tv_issue',
                'name' => 'Problema em Canais de TV',
                'subtopics' => ['Canais offline', 'Travando', 'Imagem ruim']
            ],
            [
                'id' => 'app_issue',
                'name' => 'Bug no Aplicativo',
                'subtopics' => ['App fechando sozinho', 'Erro de login', 'Lentidão']
            ],
            [
                'id' => 'billing',
                'name' => 'Assinatura / Cupons',
                'subtopics' => ['Cupom inválido', 'Plano não ativou', 'Renovação']
            ],
            [
                'id' => 'other',
                'name' => 'Outro',
                'subtopics' => []
            ]
        ]);
    }

    /**
     * Cria um ticket de suporte
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'topic' => 'required|string|max:100',
            'subtopic' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:1000'
        ]);

        $user = $request->user();

        // Limites baseados no plano: Premium = 5, Basic = 3, Free = 1
        $limit = $user->getDailyTicketLimit();

        $todayTickets = Ticket::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayTickets >= $limit) {
            return response()->json([
                'message' => "Você atingiu seu limite diário de $limit tickets. Tente novamente amanhã."
            ], 429);
        }

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'topic' => $request->topic,
            'subtopic' => $request->subtopic,
            'message' => $request->message,
            'status' => 'open'
        ]);

        return response()->json([
            'message' => 'Sua mensagem foi enviada ao suporte! Responderemos em breve.',
            'ticket_id' => $ticket->id
        ], 201);
    }
}
