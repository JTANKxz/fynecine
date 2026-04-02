<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HomeSection extends Model
{
    protected $fillable = [
        'title',
        'type',
        'content_type',
        'genre_id',
        'network_id',
        'trending_period',
        'order',
        'is_active',
        'limit',
        'content_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'limit' => 'integer',
    ];

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function network()
    {
        return $this->belongsTo(Network::class);
    }

    public function items()
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('order');
    }

    /**
     * Resolve os itens da seção baseado no tipo
     */
    public function resolveItems($limit = null)
    {
        $limit = $limit ?? $this->limit ?? 15;

        switch ($this->type) {

            case 'custom':
                return $this->resolveCustom($limit);

            case 'genre':
                return $this->resolveGenre($limit);

            case 'trending':
                return $this->resolveTrending($limit);

            case 'network':
                return $this->resolveNetwork($limit);

            case 'networks':
                return $this->resolveNetworksList($limit);

            case 'recently_added':
                return $this->resolveRecentlyAdded($limit);

            case 'events':
                return $this->resolveEvents($limit);

            default:
                return collect();
        }
    }

    private function resolveNetworksList($limit)
    {
        return Network::orderBy('name')->limit($limit)->get();
    }

    private function resolveEvents($limit)
    {
        return Event::visible()->orderBy('start_time')->limit($limit)->get();
    }

    private function resolveCustom($limit)
    {
        $items = $this->items()->get();

        return $items->map(function ($item) {
            if ($item->content_type === 'movie') {
                return Movie::find($item->content_id);
            }
            return Serie::find($item->content_id);
        })->filter()->take($limit)->values();
    }

    private function applyCategoryFilter($query)
    {
        if ($this->content_category_id) {
            $query->where('content_category_id', $this->content_category_id);
        }
        return $query;
    }

    private function resolveGenre($limit)
    {
        if (!$this->genre_id) return collect();

        $results = collect();

        if (in_array($this->content_type, ['movie', 'both'])) {
            $query = Movie::whereHas('genres', fn($q) => $q->where('genres.id', $this->genre_id));
            $this->applyCategoryFilter($query);
            $movies = $query->latest()->limit($limit)->get();
            $results = $results->merge($movies);
        }

        if (in_array($this->content_type, ['series', 'both'])) {
            $query = Serie::whereHas('genres', fn($q) => $q->where('genres.id', $this->genre_id));
            $this->applyCategoryFilter($query);
            $series = $query->latest()->limit($limit)->get();
            $results = $results->merge($series);
        }

        return $results->take($limit)->values();
    }

    private function resolveTrending($limit)
    {
        $period = $this->trending_period ?? 'all_time';

        $query = ContentView::select('content_id', 'content_type')
            ->selectRaw('COUNT(*) as views_count')
            ->groupBy('content_id', 'content_type');

        if ($period === 'today') {
            $query->whereDate('viewed_at', today());
        } elseif ($period === 'week') {
            $query->where('viewed_at', '>=', now()->subWeek());
        }

        // Filtro por content_type
        if ($this->content_type === 'movie') {
            $query->where('content_type', 'movie');
        } elseif ($this->content_type === 'series') {
            $query->where('content_type', 'series');
        }

        $trending = $query->orderByDesc('views_count')->limit($limit)->get();

        return $trending->map(function ($item) {
            if ($item->content_type === 'movie') {
                $query = Movie::where('id', $item->content_id);
            } else {
                $query = Serie::where('id', $item->content_id);
            }
            
            $this->applyCategoryFilter($query);
            $content = $query->first();
            
            if ($content) {
                $content->views_count = $item->views_count;
            }
            return $content;
        })->filter()->values();
    }

    private function resolveNetwork($limit)
    {
        if (!$this->network_id) return collect();

        $network = Network::find($this->network_id);
        if (!$network) return collect();

        $results = collect();

        if (in_array($this->content_type, ['movie', 'both'])) {
            $movieIds = \DB::table('network_content')
                ->where('network_id', $this->network_id)
                ->where('content_type', 'movie')
                ->pluck('content_id');
            
            $query = Movie::whereIn('id', $movieIds);
            $this->applyCategoryFilter($query);
            $results = $results->merge($query->latest()->limit($limit)->get());
        }

        if (in_array($this->content_type, ['series', 'both'])) {
            $serieIds = \DB::table('network_content')
                ->where('network_id', $this->network_id)
                ->where('content_type', 'series')
                ->pluck('content_id');
            
            $query = Serie::whereIn('id', $serieIds);
            $this->applyCategoryFilter($query);
            $results = $results->merge($query->latest()->limit($limit)->get());
        }

        return $results->take($limit)->values();
    }

    private function resolveRecentlyAdded($limit)
    {
        $results = collect();

        if (in_array($this->content_type, ['movie', 'both'])) {
            $query = Movie::query();
            $this->applyCategoryFilter($query);
            $results = $results->merge($query->latest()->limit($limit)->get());
        }

        if (in_array($this->content_type, ['series', 'both'])) {
            $query = Serie::query();
            $this->applyCategoryFilter($query);
            $results = $results->merge($query->latest()->limit($limit)->get());
        }

        return $results->sortByDesc('created_at')->take($limit)->values();
    }
}
