<?php

namespace App\Services\Shorts;

use App\Models\Profile;
use App\Models\Short;
use App\Models\ShortInteraction;
use Illuminate\Support\Collection;

class ShortInterestService
{
    public function forProfile(Profile $profile): array
    {
        $interactions = ShortInteraction::query()
            ->with('short:id,category,hashtags,related_type,related_id')
            ->where('profile_id', $profile->id)
            ->where('created_at', '>=', now()->subDays(60))
            ->latest()->limit(1200)->get();

        $categories = []; $hashtags = []; $related = []; $seen = [];
        foreach ($interactions as $interaction) {
            $short = $interaction->short;
            if (!$short) continue;
            $weight = $this->signalWeight($interaction->type) * $this->timeDecay($interaction->created_at);
            $categories[$short->category ?: '__uncategorized'] = ($categories[$short->category ?: '__uncategorized'] ?? 0) + $weight;
            foreach ($this->tags($short->hashtags) as $tag) $hashtags[$tag] = ($hashtags[$tag] ?? 0) + $weight;
            if ($short->related_id) $related[$short->related_type . ':' . $short->related_id] = ($related[$short->related_type . ':' . $short->related_id] ?? 0) + $weight;
            if (in_array($interaction->type, ['delivery', 'impression', 'view', 'progress', 'completion', 'skip'], true)) {
                $seen[$short->id] = max($seen[$short->id] ?? 0, $interaction->created_at->timestamp);
            }
        }

        return compact('categories', 'hashtags', 'related', 'seen');
    }

    public function tags(?string $hashtags): array
    {
        return collect(preg_split('/[\s,;]+/', mb_strtolower((string) $hashtags)))
            ->map(fn ($tag) => ltrim(trim($tag), '#'))->filter()->unique()->values()->all();
    }

    private function signalWeight(string $type): float
    {
        $defaults = ['like' => 5, 'completion' => 3, 'replay' => 4, 'view' => .5, 'progress' => .75, 'skip' => -3, 'hide' => -8, 'report' => -10];
        return (float) config('short_recommendations.signals.' . $type, $defaults[$type] ?? 0.0);
    }

    private function timeDecay($createdAt): float
    {
        $days = max(0, now()->diffInDays($createdAt));
        return max(0.15, exp(-$days / 21));
    }
}
