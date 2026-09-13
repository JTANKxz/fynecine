<?php

namespace App\Services\Shorts;

use App\Models\Short;
use Illuminate\Support\Collection;

class ShortCandidateService
{
    public function candidates(int $limit): Collection
    {
        return Short::query()
            ->with('metrics')
            ->where('is_active', true)
            ->where('availability', '!=', 'invalid')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
}
