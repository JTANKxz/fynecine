<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchProgressController extends Controller
{
    /**
     * POST /api/progress
     * Salva ou atualiza o progresso de um conteúdo
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_id' => ['required', 'string'],
            'content_type' => ['required', 'in:movie,episode'],
            'progress' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'integer', 'min:0'],
            'season_id' => ['nullable', 'integer'],
            'episode_id' => ['nullable', 'integer'],
            'guest_id' => ['nullable', 'string'],
        ]);

        $user = auth('sanctum')->user();
        
        $progress = WatchProgress::saveProgress(
            contentId: $validated['content_id'],
            progress: $validated['progress'],
            duration: $validated['duration'],
            contentType: $validated['content_type'],
            userId: $user?->id,
            guestId: $validated['guest_id'] ?? null,
            seasonId: $validated['season_id'] ?? null,
            episodeId: $validated['episode_id'] ?? null,
        );

        if (!$progress) {
            return response()->json([
                'message' => 'Progress não foi salvo (progresso < 30s ou >= 90%)',
                'removed' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Progress salvo com sucesso',
            'progress' => $progress,
        ], 200);
    }

    /**
     * GET /api/progress
     * Lista conteúdos em andamento
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 20);
        
        $user = auth('sanctum')->user();
        $guestId = $request->string('guest_id');

        $progressList = WatchProgress::inProgress(
            userId: $user?->id,
            guestId: $guestId,
            limit: $limit,
        );

        return response()->json([
            'data' => $progressList->map(fn($p) => [
                'id' => $p->id,
                'content_id' => $p->content_id,
                'content_type' => $p->content_type,
                'progress' => $p->progress,
                'duration' => $p->duration,
                'progress_percent' => $p->duration > 0 ? round(($p->progress / $p->duration) * 100, 2) : 0,
                'season_id' => $p->season_id,
                'episode_id' => $p->episode_id,
                'updated_at' => $p->updated_at,
                'content' => $p->getContentMetadata(),
            ]),
            'total' => $progressList->count(),
        ], 200);
    }

    /**
     * GET /api/progress/{content_id}
     * Obtém progresso específico de um conteúdo
     */
    public function show(string $contentId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_type' => ['required', 'in:movie,episode'],
            'guest_id' => ['nullable', 'string'],
        ]);

        $user = auth('sanctum')->user();

        $progress = WatchProgress::getProgress(
            contentId: $contentId,
            contentType: $validated['content_type'],
            userId: $user?->id,
            guestId: $validated['guest_id'] ?? null,
        );

        if (!$progress) {
            return response()->json([
                'message' => 'Nenhum progresso encontrado',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $progress->id,
                'content_id' => $progress->content_id,
                'content_type' => $progress->content_type,
                'progress' => $progress->progress,
                'duration' => $progress->duration,
                'progress_percent' => $progress->duration > 0 ? round(($progress->progress / $progress->duration) * 100, 2) : 0,
                'season_id' => $progress->season_id,
                'episode_id' => $progress->episode_id,
                'updated_at' => $progress->updated_at,
                'content' => $progress->getContentMetadata(),
            ],
        ], 200);
    }

    /**
     * DELETE /api/progress/{content_id}
     * Remove progresso de um conteúdo específico
     */
    public function destroy(string $contentId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_type' => ['required', 'in:movie,episode'],
            'guest_id' => ['nullable', 'string'],
        ]);

        $user = auth('sanctum')->user();

        $deleted = WatchProgress::removeProgress(
            contentId: $contentId,
            contentType: $validated['content_type'],
            userId: $user?->id,
            guestId: $validated['guest_id'] ?? null,
        );

        return response()->json([
            'message' => $deleted ? 'Progresso removido' : 'Nenhum progresso encontrado',
            'deleted' => $deleted,
        ], 200);
    }

    /**
     * DELETE /api/progress
     * Remove todos os progressos
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $request->validate([
            'guest_id' => ['nullable', 'string'],
        ]);

        $user = auth('sanctum')->user();

        $count = WatchProgress::clearAll(
            userId: $user?->id,
            guestId: $request->string('guest_id'),
        );

        return response()->json([
            'message' => "Removidos $count progressos",
            'count' => $count,
        ], 200);
    }
}
