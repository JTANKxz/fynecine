<?php

namespace App\Services\Shorts;

use App\Models\Profile;
use App\Models\Short;
use App\Models\ShortInteraction;
use App\Models\ShortMetric;

class ShortInteractionRecorder
{
    public function record(Profile $profile, Short $short, string $type, array $data = []): ShortInteraction
    {
        $hasViewedBefore = $type === 'view' && ShortInteraction::query()
            ->where(['short_id' => $short->id, 'profile_id' => $profile->id, 'type' => 'view'])->exists();
        $interaction = ShortInteraction::create([
            'short_id' => $short->id, 'user_id' => $profile->user_id, 'profile_id' => $profile->id,
            'type' => $type, 'watch_seconds' => $data['watch_seconds'] ?? null,
            'watch_percentage' => $data['watch_percentage'] ?? null,
            'session_id' => $data['session_id'] ?? null, 'metadata' => $data['metadata'] ?? null,
        ]);
        $this->updateMetrics($short, $profile, $type, $data, $hasViewedBefore);
        return $interaction;
    }

    public function toggleLike(Profile $profile, Short $short, array $data = []): bool
    {
        $existing = ShortInteraction::query()->where(['short_id' => $short->id, 'profile_id' => $profile->id, 'type' => 'like'])->latest()->first();
        $metric = ShortMetric::firstOrCreate(['short_id' => $short->id]);
        if ($existing) {
            $existing->delete();
            // Shorts antigos podem já ter likes antes da tabela agregada existir.
            $metric->likes = max(0, (int) $metric->likes - 1);
            $metric->save();
            return false;
        }
        $this->record($profile, $short, 'like', $data);
        return true;
    }

    private function updateMetrics(Short $short, Profile $profile, string $type, array $data, bool $hasViewedBefore = false): void
    {
        if (in_array($type, ['delivery', 'impression', 'progress', 'hide', 'report', 'save'], true)) return;
        $metric = ShortMetric::firstOrCreate(['short_id' => $short->id]);
        $increments = match ($type) {
            'view' => ['views' => 1], 'completion' => ['completions' => 1], 'skip' => ['skips' => 1],
            'replay' => ['replays' => 1], 'like' => ['likes' => 1], default => [],
        };
        foreach ($increments as $column => $amount) $metric->increment($column, $amount);
        if ($type === 'view' && !$hasViewedBefore) $metric->increment('unique_viewers');
        if (!empty($data['watch_seconds'])) $metric->increment('total_watch_seconds', (int) $data['watch_seconds']);
        if (isset($data['watch_percentage'])) {
            $count = max(1, $metric->views + $metric->completions);
            $metric->average_completion = round((((float) $metric->average_completion * max(0, $count - 1)) + (float) $data['watch_percentage']) / $count, 2);
        }
        $metric->engagement_score = round(($metric->completions * 2) + ($metric->likes * 3) + $metric->replays - ($metric->skips * 1.5), 2);
        $metric->last_engagement_at = now(); $metric->save();
    }
}
