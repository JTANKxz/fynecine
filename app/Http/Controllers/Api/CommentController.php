<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Retorna lista de comentários de um Filme/Serie específico
     * 
     * GET /api/movies/{id}/comments
     * GET /api/series/{id}/comments
     */
    public function index($type, $idOrSlug): JsonResponse
    {
        $config = \App\Models\AppConfig::getSettings();
        
        if (!$config->comments_status) {
            return response()->json([
                'message' => 'Os comentários estão desativados globalmente.'
            ], 403);
        }

        $modelStr = $type === 'movies' ? Movie::class : Serie::class;
        
        $content = $modelStr::where(function ($query) use ($idOrSlug) {
            if (is_numeric($idOrSlug)) {
                $query->where('id', $idOrSlug);
            } else {
                $query->where('slug', $idOrSlug);
            }
        })->firstOrFail();

        $comments = $content->comments()
            ->where('approved', true) // Apenas aprovados
            ->with(['user:id,name,avatar']) // Traz dados do usuário (avatar pra thumbnail)
            ->paginate(15);

        return response()->json($comments);
    }

    /**
     * Adiciona um comentário a um Filme ou Série (Requer Autenticação)
     * POST /api/movies/{id}/comments
     * POST /api/series/{id}/comments
     */
    public function store(Request $request, $type, $idOrSlug): JsonResponse
    {
        $config = \App\Models\AppConfig::getSettings();
        
        if (!$config->comments_status) {
            return response()->json([
                'message' => 'Os comentários estão desativados na plataforma.'
            ], 403);
        }

        $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $modelStr = $type === 'movies' ? Movie::class : Serie::class;

        $content = $modelStr::where(function ($query) use ($idOrSlug) {
            if (is_numeric($idOrSlug)) {
                $query->where('id', $idOrSlug);
            } else {
                $query->where('slug', $idOrSlug);
            }
        })->firstOrFail();

        // Opcional: Anti-spam (1 comentario a cada X min), limitamos nas constraints de frontend pro agora.
        
        $comment = $content->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $request->body,
            'approved' => (bool) $config->comments_auto_approve
        ]);

        // Retorna o formato para anexar na listagem dinamicamente
        return response()->json([
            'message' => 'Comentário enviado!',
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at,
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'avatar' => $request->user()->avatar,
                ]
            ]
        ], 201);
    }

    /**
     * Usuário deleta o próprio comentário.
     * DELETE /api/comments/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Você não tem permissão para excluir este comentário.'
            ], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comentário excluído com sucesso.']);
    }
}
