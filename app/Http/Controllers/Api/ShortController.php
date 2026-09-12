<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Serie;
use App\Models\Short;
use App\Models\ShortInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortController extends Controller
{
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

    public function feed(Request $request): JsonResponse
    {
        $profile = $this->profile($request);
        $limit = min(max($request->integer('limit', 12), 1), 30);
        $excluded = collect();
        $recent = collect();
        if ($profile) {
            $excluded = ShortInteraction::query()->where('profile_id', $profile->id)
                ->whereIn('type', ['like', 'hide', 'report'])->pluck('short_id');
            $recent = ShortInteraction::query()->where('profile_id', $profile->id)->where('type', 'impression')
                ->where('created_at', '>', now()->subDays(2))->latest()->limit(50)->pluck('short_id');
        }
        $shorts = Short::query()->where('is_active', true)->where('availability', '!=', 'invalid')
            ->whereNotIn('id', $excluded)->whereNotIn('id', $recent)
            ->orderByDesc('published_at')->inRandomOrder()->limit($limit)->get();
        if ($shorts->count() < $limit) {
            $shorts = Short::query()->where('is_active', true)->where('availability', '!=', 'invalid')
                ->whereNotIn('id', $excluded)->inRandomOrder()->limit($limit)->get();
        }
        if ($profile) {
            foreach ($shorts as $short) {
                ShortInteraction::updateOrCreate(
                    ['short_id' => $short->id, 'user_id' => $profile->user_id, 'profile_id' => $profile->id, 'type' => 'impression'],
                    ['metadata' => ['source' => 'feed']]
                );
            }
        }
        return response()->json(['data' => $shorts->map(fn (Short $short) => $this->payload($short, $profile)), 'next_cursor' => null]);
    }

    public function interaction(Request $request, Short $short): JsonResponse
    {
        $profile = $this->profile($request);
        $data = $request->validate(['type' => 'required|in:like,hide,save,report,completion', 'watch_seconds' => 'nullable|integer|min:0']);
        $key = ['short_id' => $short->id, 'user_id' => $request->user()->id, 'profile_id' => $profile->id, 'type' => $data['type']];
        $existing = ShortInteraction::where($key)->first();
        if ($data['type'] === 'like' && $existing) {
            $existing->delete();
            return response()->json(['active' => false, 'message' => 'Curtida removida.']);
        }
        ShortInteraction::updateOrCreate($key, ['watch_seconds' => $data['watch_seconds'] ?? null]);
        return response()->json(['active' => true, 'message' => 'Interação registrada.']);
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
