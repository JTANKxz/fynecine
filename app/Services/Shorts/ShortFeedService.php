<?php

namespace App\Services\Shorts;

use App\Models\Profile;
use App\Models\Short;
use App\Models\ShortInteraction;
use Illuminate\Support\Collection;

class ShortFeedService
{
    public function __construct(
        private readonly ShortCandidateService $candidates,
        private readonly ShortInterestService $interests,
        private readonly ShortScoringService $scoring,
        private readonly ShortInteractionRecorder $recorder,
    ) {}

    public function generate(?Profile $profile, int $limit): Collection
    {
        $signals = $profile ? $this->interests->forProfile($profile) : ['categories' => [], 'hashtags' => [], 'related' => [], 'seen' => []];
        $candidateLimit = (int) config('short_recommendations.candidate_limit', 250);
        $rejectedIds = $profile
            ? ShortInteraction::query()->where('profile_id', $profile->id)->whereIn('type', ['hide', 'report'])->pluck('short_id')->all()
            : [];
        $all = $this->candidates->candidates($candidateLimit)
            ->whereNotIn('id', $rejectedIds)
            ->map(fn (Short $short) => ['short' => $short, 'score' => $this->scoring->score($short, $signals)])
            ->sortByDesc('score')->values();

        // Catálogo pequeno: relaxa exclusões e mantém os itens antigos disponíveis.
        if ($all->count() < $limit) {
            $all = $this->candidates->candidates(max($limit, $candidateLimit))
                ->map(fn (Short $short) => ['short' => $short, 'score' => $this->scoring->score($short, $signals)])
                ->sortByDesc('score')->values();
        }

        $result = $this->diversify($this->mixCandidates($all, $limit), $limit);
        if ($profile) foreach ($result as $short) $this->recorder->record($profile, $short, 'delivery', ['metadata' => ['source' => 'dynamic_feed']]);
        return $result;
    }

    private function diversify(Collection $ranked, int $limit): Collection
    {
        $maxCategory = (int) config('short_recommendations.max_same_category_in_row', 2);
        $maxRelated = (int) config('short_recommendations.max_same_related_in_row', 1);
        $selected = collect(); $categoryRun = 0; $relatedRun = 0; $previousCategory = null; $previousRelated = null;

        // Pequena variação apenas entre posições com score equivalente: evita feed previsível.
        $pool = $ranked->chunk(8)->flatMap(fn (Collection $group) => $group->shuffle())->values();
        while ($selected->count() < $limit && $pool->isNotEmpty()) {
            $index = $pool->search(function (array $entry) use ($previousCategory, $categoryRun, $maxCategory, $previousRelated, $relatedRun, $maxRelated) {
                $short = $entry['short']; $category = $short->category ?: '__uncategorized';
                $related = $short->related_id ? $short->related_type . ':' . $short->related_id : null;
                return !($category === $previousCategory && $categoryRun >= $maxCategory)
                    && !($related && $related === $previousRelated && $relatedRun >= $maxRelated);
            });
            if ($index === false) $index = 0; // relaxamento progressivo para catálogos pequenos.
            $entry = $pool->get($index);
            $pool->forget($index);
            $pool = $pool->values();
            if (!$entry || !isset($entry['short'])) continue;
            $short = $entry['short'];
            $category = $short->category ?: '__uncategorized'; $related = $short->related_id ? $short->related_type . ':' . $short->related_id : null;
            $categoryRun = $category === $previousCategory ? $categoryRun + 1 : 1;
            $relatedRun = $related && $related === $previousRelated ? $relatedRun + 1 : 1;
            $previousCategory = $category; $previousRelated = $related;
            $selected->push($short);
        }
        return $selected->values();
    }

    /** Mistura qualidade consolidada, tendência e descoberta sem sortear o catálogo inteiro. */
    private function mixCandidates(Collection $ranked, int $limit): Collection
    {
        if ($ranked->count() <= $limit) return $ranked;
        $mix = config('short_recommendations.mix', []) ?: [];
        $qualityCount = max(1, (int) ceil($limit * (float) ($mix['quality'] ?? .60)));
        $trendingCount = max(1, (int) ceil($limit * (float) ($mix['trending'] ?? .25)));
        $exploreCount = max(1, $limit - $qualityCount - $trendingCount);

        $quality = $ranked->take(max($qualityCount * 3, $limit));
        $trending = $ranked->sortByDesc(fn (array $entry) => (float) ($entry['short']->metrics?->engagement_score ?? 0))->take($trendingCount * 3);
        $explore = $ranked->slice((int) floor($ranked->count() * .30))->shuffle()->take($exploreCount * 3);
        return $quality->merge($trending)->merge($explore)
            ->unique(fn (array $entry) => $entry['short']->id)->values();
    }
}
