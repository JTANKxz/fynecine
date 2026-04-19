<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\Profile;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    private function getProfile(Request $request): Profile
    {
        $profileId = $request->header('Profile-Id') ?: $request->header('X-Profile-Id');
        if (!$profileId) abort(400, 'Header Profile-Id é obrigatório.');
        return $request->user()->profiles()->findOrFail($profileId);
    }

    public function index(Request $request): JsonResponse
    {
        $profile = $this->getProfile($request);
        $user = $request->user();

        $playlists = Playlist::where('profile_id', $profile->id)
            ->with(['items' => function ($query) {
                $query->latest()->limit(4)->with('listable');
            }])
            ->latest()
            ->get()
            ->map(function ($playlist, $index) use ($user) {
                // Lógica de bloqueio: se não for premium e for acima da 3ª playlist
                $isLocked = !$user->hasPlan && $index >= 3;

                return [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                    'is_locked' => $isLocked,
                    'items_count' => $playlist->items()->count(),
                    'collage_posters' => $playlist->items->map(function ($item) {
                        return $item->listable ? ($item->listable->poster ?? $item->listable->poster_path) : null;
                    })->filter()->values(),
                ];
            });

        return response()->json($playlists);
    }

    public function store(Request $request): JsonResponse
    {
        $profile = $this->getProfile($request);
        $user = $request->user();

        // Verificar limite para usuários Free
        if (!$user->hasPlan) {
            $count = Playlist::where('profile_id', $profile->id)->count();
            if ($count >= 3) {
                return response()->json(['message' => 'Usuários gratuitos podem criar no máximo 3 playlists.'], 403);
            }
        }

        $request->validate(['name' => 'required|string|max:100']);

        $playlist = Playlist::create([
            'profile_id' => $profile->id,
            'name' => $request->name,
        ]);

        return response()->json($playlist, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $profile = $this->getProfile($request);
        $playlist = Playlist::where('id', $id)->where('profile_id', $profile->id)->firstOrFail();
        
        $user = $request->user();
        // Se estiver bloqueada para free user
        $playlists = Playlist::where('profile_id', $profile->id)->latest()->get();
        $index = $playlists->search(fn($p) => $p->id === $playlist->id);
        
        if (!$user->hasPlan && $index >= 3) {
            return response()->json(['message' => 'Esta playlist está bloqueada no seu plano atual.'], 403);
        }

        $items = $playlist->items()
            ->with('listable')
            ->latest()
            ->get()
            ->map(function ($item) {
                if (!$item->listable) return null;
                return [
                    'item_id' => $item->id,
                    'type' => class_basename($item->listable_type),
                    'added_at' => $item->created_at,
                    'content' => $item->listable,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'id' => $playlist->id,
            'name' => $playlist->name,
            'items' => $items
        ]);
    }

    public function toggleItem(Request $request): JsonResponse
    {
        $profile = $this->getProfile($request);
        
        $request->validate([
            'playlist_id' => 'required|exists:playlists,id',
            'type' => 'required|in:movie,serie',
            'id' => 'required|integer',
        ]);

        $playlist = Playlist::where('id', $request->playlist_id)->where('profile_id', $profile->id)->firstOrFail();
        
        $class = $request->type === 'movie' ? Movie::class : Serie::class;
        
        $existing = PlaylistItem::where('playlist_id', $playlist->id)
            ->where('listable_id', $request->id)
            ->where('listable_type', $class)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['in_playlist' => false, 'message' => 'Removido da playlist.']);
        }

        PlaylistItem::create([
            'playlist_id' => $playlist->id,
            'listable_id' => $request->id,
            'listable_type' => $class,
        ]);

        return response()->json(['in_playlist' => true, 'message' => 'Adicionado à playlist.'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = $this->getProfile($request);
        $playlist = Playlist::where('id', $id)->where('profile_id', $profile->id)->firstOrFail();
        $playlist->delete();

        return response()->json(['message' => 'Playlist removida.']);
    }
}
