<?php

namespace App\Services\Shorts;

use App\Models\Short;

class ShortScoringService
{
    public function __construct(private readonly ShortInterestService $interests) {}

    public function score(Short $short, array $profileSignals = []): float
    {
        $w = array_merge([
            'user_category' => 8.0, 'user_hashtag' => 5.0, 'user_related' => 13.0,
            'global_views' => 2.0, 'global_completion' => 18.0, 'global_likes' => 7.0,
            'recent_performance' => 5.0, 'newness' => 5.0, 'exploration_jitter' => 7.0,
            'recent_delivery_penalty' => 45.0, 'recent_view_penalty' => 32.0,
        ], config('short_recommendations.weights', []) ?: []);
        $metric = $short->metrics;
        $category = $short->category ?: '__uncategorized';
        $score = 0.0;

        $score += ($profileSignals['categories'][$category] ?? 0) * $w['user_category'];
        foreach ($this->interests->tags($short->hashtags) as $tag) $score += ($profileSignals['hashtags'][$tag] ?? 0) * $w['user_hashtag'];
        if ($short->related_id) $score += ($profileSignals['related'][$short->related_type . ':' . $short->related_id] ?? 0) * $w['user_related'];

        if ($metric) {
            $score += log(1 + $metric->views) * $w['global_views'];
            $score += ((float) $metric->average_completion / 100) * $w['global_completion'];
            $score += log(1 + $metric->likes) * $w['global_likes'];
            $score += (float) $metric->engagement_score * $w['recent_performance'];
        }

        $hours = $short->published_at ? max(0, now()->diffInHours($short->published_at)) : 8760;
        $score += max(0, 1 - ($hours / (24 * 21))) * $w['newness'];
        $score += (mt_rand(-100, 100) / 100) * $w['exploration_jitter'];

        $lastSeen = $profileSignals['seen'][$short->id] ?? null;
        if ($lastSeen) {
            $ageHours = max(0, (now()->timestamp - $lastSeen) / 3600);
            $recentHours = (int) config('short_recommendations.recent_delivery_hours', 24);
            if ($ageHours < $recentHours) $score -= $w['recent_delivery_penalty'] * (1 - $ageHours / $recentHours);
            elseif ($ageHours < 24 * 14) $score -= $w['recent_view_penalty'] * (1 - (($ageHours - 24) / (24 * 13)));
        }
        return $score;
    }
}
