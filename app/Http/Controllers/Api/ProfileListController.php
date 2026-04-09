<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Profile;
use App\Models\ProfileList;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileListController extends Controller
{
    /**
     * Obtém o perfil autenticado a partir do Header "Profile-Id".
     */
    private function getProfile(Request $request): Profile
    {
        $profileId = $request->header('Profile-Id') ?: $request->header('X-Profile-Id');
        
        if (!$profileId) {
            abort(400, 'Header Profile-Id é obrigatório.');
        }

        // Garante que o perfil passado pertence ao usuário autenticado
        return $request->user()->profiles()->findOrFail($profileId);
    }

    /**
     * Retorna toda a lista do perfil atual.
     * GET /api/list
     */
    public function index(Request $request): JsonResponse
    {
        $profile = $this->getProfile($request);

        $items = $profile
            ->lists()
            ->with('listable')
            ->latest()
            ->get()
            ->map(function ($item) {
                if (!$item->listable) return null;

                return [
                    'list_id'  => $item->id,
                    'type'     => class_basename($item->listable_type), // "Movie" ou "Serie"
                    'added_at' => $item->created_at,
                    'content'  => $item->listable,
                ];
            })
            ->filter()
            ->values();

        return response()->json($items);
    }

    /**
     * Adiciona ou remove um item da lista (toggle).
     * POST /api/list/toggle (Body: { "type": "movie"|"serie", "id": 42 })
     */
    public function toggle(Request $request): JsonResponse
    {
        $profile = $this->getProfile($request);

        $request->validate([
            'type' => ['required', 'in:movie,serie'],
            'id'   => ['required', 'integer'],
        ]);

        [$model, $class] = $this->resolveModel($request->type);
        $content = $model::findOrFail($request->id);

        $existing = ProfileList::where('profile_id', $profile->id)
            ->where('listable_id', $content->id)
            ->where('listable_type', $class)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'in_list' => false,
                'message' => 'Removido da lista.',
            ]);
        }

        $item = ProfileList::create([
            'profile_id'    => $profile->id,
            'listable_id'   => $content->id,
            'listable_type' => $class,
        ]);

        return response()->json([
            'in_list' => true,
            'list_id' => $item->id,
            'message' => 'Adicionado à lista.',
        ], 201);
    }

    /**
     * Verifica se um item está na lista.
     * GET /api/list/check?type=movie&id=42
     */
    public function check(Request $request): JsonResponse
    {
        $profile = $this->getProfile($request);

        $request->validate([
            'type' => ['required', 'in:movie,serie'],
            'id'   => ['required', 'integer'],
        ]);

        [, $class] = $this->resolveModel($request->type);

        $inList = ProfileList::where('profile_id', $profile->id)
            ->where('listable_id', $request->id)
            ->where('listable_type', $class)
            ->exists();

        return response()->json(['in_list' => $inList]);
    }

    /**
     * Remove um item da lista pelo ID da entrada.
     * DELETE /api/list/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = $this->getProfile($request);

        $item = ProfileList::where('id', $id)
            ->where('profile_id', $profile->id)
            ->firstOrFail();

        $item->delete();

        return response()->json(['message' => 'Removido da lista.']);
    }

    /**
     * Helper para converter string pro Model class.
     */
    private function resolveModel(string $type): array
    {
        return match ($type) {
            'movie' => [new Movie(), Movie::class],
            'serie' => [new Serie(), Serie::class],
        };
    }
}
