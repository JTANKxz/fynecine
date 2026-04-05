<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\ContentRequest;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RequestController extends Controller
{
    /**
     * Busca no TMDB e avisa se o Title já existe
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json([]);
        }

        $config = AppConfig::getSettings();
        if (!$config->tmdb_key) {
            return response()->json(['error' => 'API TMDB não configurada.'], 500);
        }

        $response = Http::get("https://api.themoviedb.org/3/search/multi", [
            'api_key' => $config->tmdb_key,
            'query' => $query,
            'language' => 'pt-BR',
            'include_adult' => false
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Falha no TMDB'], 502);
        }

        // Recupera apenas movies e tv shows, exclui person
        $results = collect($response->json('results'))
            ->filter(fn($item) => in_array($item['media_type'], ['movie', 'tv']))
            ->map(function ($item) {
                
                $type = $item['media_type'];
                $tmdb_id = (string)$item['id'];
                
                // Checa se já temos importado
                $exists = false;
                if ($type === 'movie') {
                    $exists = Movie::where('tmdb_id', $tmdb_id)->exists();
                } else {
                    $exists = Serie::where('tmdb_id', $tmdb_id)->exists();
                }

                // Checa se usuário logado já pediu isso e tá pendente
                $alreadyRequested = false;
                if ($user = auth('sanctum')->user()) {
                    $alreadyRequested = ContentRequest::where('user_id', $user->id)
                        ->where('tmdb_id', $tmdb_id)
                        ->exists();
                }

                return [
                    'tmdb_id' => $tmdb_id,
                    'type' => $type,
                    'title' => $type === 'movie' ? ($item['title'] ?? '') : ($item['name'] ?? ''),
                    'year' => $type === 'movie' 
                        ? substr($item['release_date'] ?? '', 0, 4) 
                        : substr($item['first_air_date'] ?? '', 0, 4),
                    'poster' => isset($item['poster_path']) ? 'https://image.tmdb.org/t/p/w200'.$item['poster_path'] : null,
                    'already_in_db' => $exists,
                    'already_requested' => $alreadyRequested
                ];
            })
            ->values();

        return response()->json($results);
    }

    /**
     * Efetua um Pedido validando Cota do Plano
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tmdb_id' => 'nullable|string',
            'type' => 'required|in:movie,tv',
            'title' => 'required|string',
            'year' => 'required|string'
        ]);

        $user = $request->user();
        
        // Verifica Cota (Free=1, Basic=3, Premium=5)
        $limit = $user->getDailyRequestLimit();

        $requestsToday = ContentRequest::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($requestsToday >= $limit) {
            return response()->json([
                'message' => "Você atingiu o limite diário de pedidos ({$limit}/dia).",
                'upgrade_required' => $user->plan_type !== 'premium'
            ], 429);
        }

        // Previne Duplicidades Pessoais (apenas se tiver tmdb_id)
        if ($request->tmdb_id && ContentRequest::where('user_id', $user->id)->where('tmdb_id', $request->tmdb_id)->exists()) {
            return response()->json(['message' => 'Você já pediu este título.'], 409);
        }

        // Salva
        $contentRequest = ContentRequest::create([
            'user_id' => $user->id,
            'tmdb_id' => $request->tmdb_id,
            'type'    => $request->type,
            'title'   => $request->title,
            'year'    => $request->year,
            'status'  => 'pending'
        ]);

        return response()->json([
            'message' => 'Pedido enviado com sucesso!',
            'requests_remaining' => $limit - ($requestsToday + 1)
        ], 201);
    }
}
