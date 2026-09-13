<?php

namespace App\Console\Commands;

use App\Models\Short;
use App\Models\ShortInteraction;
use App\Models\ShortMetric;
use Illuminate\Console\Command;

class RebuildShortMetrics extends Command
{
    protected $signature = 'shorts:rebuild-metrics';
    protected $description = 'Reconstrói métricas agregadas de Shorts a partir do histórico de interações';

    public function handle(): int
    {
        Short::query()->select('id')->chunkById(100, function ($shorts) {
            foreach ($shorts as $short) {
                $interactions = ShortInteraction::query()->where('short_id', $short->id);
                $views = (clone $interactions)->where('type', 'view')->count();
                $completions = (clone $interactions)->where('type', 'completion')->count();
                $skips = (clone $interactions)->where('type', 'skip')->count();
                $replays = (clone $interactions)->where('type', 'replay')->count();
                $likes = (clone $interactions)->where('type', 'like')->count();
                $percentages = (clone $interactions)->whereNotNull('watch_percentage');
                $last = (clone $interactions)->latest()->value('created_at');
                ShortMetric::updateOrCreate(['short_id' => $short->id], [
                    'views' => $views,
                    'unique_viewers' => (clone $interactions)->where('type', 'view')->distinct('profile_id')->count('profile_id'),
                    'likes' => $likes, 'skips' => $skips, 'replays' => $replays, 'completions' => $completions,
                    'total_watch_seconds' => (int) ((clone $interactions)->sum('watch_seconds') ?: 0),
                    'average_completion' => (float) ((clone $percentages)->avg('watch_percentage') ?: 0),
                    'engagement_score' => round(($completions * 2) + ($likes * 3) + $replays - ($skips * 1.5), 2),
                    'last_engagement_at' => $last,
                ]);
            }
        });
        $this->info('Métricas de Shorts reconstruídas.');
        return self::SUCCESS;
    }
}
