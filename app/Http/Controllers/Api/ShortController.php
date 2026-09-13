<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Serie;
use App\Models\Short;
use App\Models\ShortInteraction;
use App\Models\AppConfig;
use App\Services\Shorts\ShortFeedService;
use App\Services\Shorts\ShortInteractionRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortController extends Controller
{
    private function ensureEnabled(): void
    {
        abort_unless(AppConfig::getSettings()->shorts_enabled, 404);
    }

    private function profile(Request $request): ?Profile
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return null;
        }

        $id = $request->header('Profile-Id') ?: $request->header('X-Profile-Id');
        if (!$id) {
            return null;
        }

        return $user->profiles()->findOrFail($id);
    }

    public function feed(Request $request, ShortFeedService $feed): JsonResponse
    {
        $this->ensureEnabled();
        $profile = $this->profile($request);
        $limit = min(max($request->integer('limit', 12), 1), 30);
        $shorts = $feed->generate($profile, $limit);
        return response()->json([
            'data' => $shorts->map(fn (Short $short) => $this->payload($short, $profile)),
            'next_cursor' => null,
            'ranking' => 'dynamic',
        ]);
    }

    public function interaction(Request $request, Short $short, ShortInteractionRecorder $recorder): JsonResponse
    {
        $this->ensureEnabled();
        $profile = $this->profile($request);
        abort_unless($profile, 400, 'Header Profile-Id é obrigatório.');
        $data = $request->validate([
            'type' => 'required|in:like,hide,save,report,delivery,view,progress,completion,skip,replay',
            'watch_seconds' => 'nullable|integer|min:0', 'watch_percentage' => 'nullable|integer|min:0|max:100',
            'session_id' => 'nullable|uuid',
        ]);
        if ($data['type'] === 'like') {
            $active = $recorder->toggleLike($profile, $short, $data);
            return response()->json(['active' => $active, 'message' => $active ? 'Curtida registrada.' : 'Curtida removida.']);
        }
        $recorder->record($profile, $short, $data['type'], $data);
        return response()->json(['active' => true, 'message' => 'Interação registrada.']);
    }

    public function show(Request $request, Short $short): JsonResponse
    {
        $this->ensureEnabled();
        abort_unless($short->is_active && $short->availability !== 'invalid', 404);
        return response()->json(['data' => $this->payload($short, $this->profile($request))]);
    }

    public function liked(Request $request): JsonResponse
    {
        $this->ensureEnabled();
        $profile = $this->profile($request);
        abort_unless($profile, 400, 'Header Profile-Id é obrigatório.');
        $shorts = Short::query()->whereHas('interactions', fn ($q) => $q
            ->where('profile_id', $profile->id)->where('type', 'like'))
            ->latest()->get();
        return response()->json(['data' => $shorts->map(fn (Short $short) => $this->payload($short, $profile))]);
    }

    private function payload(Short $short, ?Profile $profile): array
    {
        return [
            'id' => $short->id, 'title' => $short->title, 'description' => $short->description,
            'source_provider' => $short->source_provider, 'source_url' => $short->source_url,
            'playback_kind' => $short->playback_kind, 'embed_url' => $short->embed_url,
            'thumbnail_url' => $short->thumbnail_url, 'duration_seconds' => $short->duration_seconds,
            'hashtags' => $short->hashtags, 'category' => $short->category,
            'related_content' => $this->relatedPayload($short),
            'is_liked' => $profile
                ? $short->interactions()->where('profile_id', $profile->id)->where('type', 'like')->exists()
                : false,
        ];
    }

    private function relatedPayload(Short $short): ?array
    {
        if (!$short->related_type || !$short->related_id) {
            return null;
        }

        $payload = [
            'type' => $short->related_type,
            'id' => $short->related_id,
            'title' => $short->related_title,
            'target_type' => $short->related_type,
            'target_id' => $short->related_id,
            'target_slug' => null,
            'season_number' => null,
        ];

        if ($short->related_type === 'movie') {
            $movie = Movie::find($short->related_id);
            $payload['target_slug'] = $movie?->slug;
        } elseif ($short->related_type === 'series') {
            $serie = Serie::find($short->related_id);
            $payload['target_slug'] = $serie?->slug;
        } elseif ($short->related_type === 'season') {
            $season = Season::with('series')->find($short->related_id);
            if ($season?->series) {
                $payload['target_type'] = 'series';
                $payload['target_id'] = $season->series->id;
                $payload['target_slug'] = $season->series->slug;
                $payload['season_number'] = $season->season_number;
            }
        }

        return $payload;
    }
}
