<?php

namespace App\Services;

use App\Models\HomeSection;
use App\Models\Movie;
use App\Models\Serie;

/**
 * Resolve, em uma única leitura por request, a posição de cada conteúdo nas
 * seções Top 10 ativas. O Top 10 geral sempre vence o ranking de uma categoria.
 */
class TopRankingService
{
    private ?array $rankings = null;

    public function for(string $type, int $contentId): ?array
    {
        $key = $this->key($type, $contentId);

        return $this->all()[$key] ?? null;
    }

    public function decorate($items)
    {
        return collect($items)->map(function ($item) {
            $type = $item instanceof Movie ? 'movie' : ($item instanceof Serie ? 'series' : null);
            if (!$type) {
                return $item;
            }

            // As seções da home serializam o model diretamente. Sempre exponha
            // a tag calculada para que tags com prazo vencido não cheguem ao app.
            $item->setAttribute('tag_text', $item->api_tag_text);

            $ranking = $this->for($type, $item->id);
            if ($ranking) {
                $item->setAttribute('top_rank', $ranking['rank']);
                $item->setAttribute('top_label', $ranking['label']);
                $item->setAttribute('top_is_general', $ranking['is_general']);
            }

            return $item;
        })->values();
    }

    private function all(): array
    {
        if ($this->rankings !== null) {
            return $this->rankings;
        }

        $rankings = [];
        $sections = HomeSection::query()
            ->with('category')
            ->where('is_active', true)
            // Instalações antigas podiam ter sido criadas como "trending"
            // com título Top 10; ambas as configurações representam ranking.
            ->where(function ($query) {
                $query->where('type', 'top_10')
                    ->orWhereRaw('LOWER(title) LIKE ?', ['%top 10%']);
            })
            ->orderBy('order')
            ->get();

        foreach ($sections as $section) {
            $isGeneral = $section->content_category_id === null;
            $label = $isGeneral ? 'Fynecine' : ($section->category?->name ?: $section->title);

            foreach ($section->resolveItems(10)->values() as $index => $item) {
                $type = $item instanceof Movie ? 'movie' : ($item instanceof Serie ? 'series' : null);
                if (!$type) {
                    continue;
                }

                $key = $this->key($type, $item->id);
                // O ranking geral tem prioridade sobre qualquer ranking de seção.
                if (!isset($rankings[$key]) || $isGeneral) {
                    $rankings[$key] = [
                        'rank' => $index + 1,
                        'label' => $label,
                        'is_general' => $isGeneral,
                    ];
                }
            }
        }

        return $this->rankings = $rankings;
    }

    private function key(string $type, int $id): string
    {
        return $type . ':' . $id;
    }
}
