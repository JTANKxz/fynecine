<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RelatedContentService
{
    public function for(Model $content, int $limit = 12): Collection
    {
        $class = $content instanceof Movie ? Movie::class : Serie::class;
        $content->loadMissing(['genres', 'keywords', 'cast']);

        $candidates = $class::query()
            ->whereKeyNot($content->getKey())
            ->with(['genres', 'keywords', 'cast'])
            ->get();

        $scored = $candidates->map(function (Model $candidate) use ($content) {
            return ['content' => $candidate, 'score' => $this->score($content, $candidate)];
        })->filter(fn (array $item) => $item['score'] > 0)
          ->sortByDesc('score')
          ->take(80)
          ->values();

        $selected = collect();
        $daySeed = now()->format('o-W');
        while ($selected->count() < $limit && $scored->isNotEmpty()) {
            $next = $scored->map(function (array $item) use ($selected, $content, $daySeed) {
                $penalty = $selected->isEmpty() ? 0 : $selected->max(fn (array $chosen) => $this->similarityPenalty($item['content'], $chosen['content']));
                $tie = hexdec(substr(sha1($daySeed . '|' . $content->getKey() . '|' . $item['content']->getKey()), 0, 4)) / 65535;
                $item['effective_score'] = $item['score'] - $penalty + ($tie * 0.35);
                return $item;
            })->sortByDesc('effective_score')->first();

            $selected->push($next);
            $scored = $scored->reject(fn (array $item) => $item['content']->getKey() === $next['content']->getKey())->values();
        }

        return $selected->pluck('content')->values();
    }

    private function score(Model $source, Model $candidate): float
    {
        $sourceGenres = $source->genres->pluck('id')->all();
        $sharedGenres = count(array_intersect($sourceGenres, $candidate->genres->pluck('id')->all()));
        $genreScore = $sourceGenres ? min(42, 42 * ($sharedGenres / count($sourceGenres))) : 0;

        $sourceKeywords = $source->keywords->pluck('tmdb_id')->all();
        $sharedKeywords = count(array_intersect($sourceKeywords, $candidate->keywords->pluck('tmdb_id')->all()));
        $keywordScore = $sourceKeywords ? min(25, 25 * ($sharedKeywords / count($sourceKeywords))) : 0;

        $sourceCast = $source->cast->keyBy('id');
        $candidateCast = $candidate->cast->keyBy('id');
        $castScore = 0;
        foreach ($sourceCast as $id => $actor) {
            if (!$candidateCast->has($id)) continue;
            $sourceOrder = (int) ($actor->pivot->order ?? 99);
            $candidateOrder = (int) ($candidateCast[$id]->pivot->order ?? 99);
            $castScore += ($sourceOrder < 3 || $candidateOrder < 3) ? 9 : 4;
        }
        $castScore = min(18, $castScore);

        $difference = abs($this->ageValue($source->age_rating) - $this->ageValue($candidate->age_rating));
        $ageScore = match (true) {
            $difference === 0 => 10,
            $difference <= 2 => 8,
            $difference <= 4 => 6,
            $difference <= 6 => 3,
            default => 0,
        };
        $ratingScore = min(5, max(0, ((float) $candidate->rating / 10) * 5));

        return $genreScore + $keywordScore + $castScore + $ageScore + $ratingScore;
    }

    private function similarityPenalty(Model $candidate, Model $chosen): float
    {
        $genres = $candidate->genres->pluck('id')->all();
        $chosenGenres = $chosen->genres->pluck('id')->all();
        $genreOverlap = count(array_intersect($genres, $chosenGenres)) / max(1, count(array_unique(array_merge($genres, $chosenGenres))));
        $keywords = $candidate->keywords->pluck('tmdb_id')->all();
        $chosenKeywords = $chosen->keywords->pluck('tmdb_id')->all();
        $keywordOverlap = count(array_intersect($keywords, $chosenKeywords)) / max(1, count(array_unique(array_merge($keywords, $chosenKeywords))));
        $sharedCast = count(array_intersect($candidate->cast->pluck('id')->all(), $chosen->cast->pluck('id')->all()));
        return min(12, ($genreOverlap * 7) + ($keywordOverlap * 3) + min(2, $sharedCast));
    }

    private function ageValue(?string $rating): int
    {
        $rating = strtoupper(trim((string) $rating));
        if (in_array($rating, ['L', 'LIVRE', 'G'])) return 0;
        if (preg_match('/(10|12|14|16|18)/', $rating, $match)) return (int) $match[1];
        return 12;
    }
}
