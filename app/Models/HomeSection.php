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

            case 'top_10':
                return $this->resolveTrending(10);

            case 'upcoming':
                return $this->resolveUpcoming($limit);

            default:
                return collect();
        }
    }

    private function resolveUpcoming($limit)
    {
        // Exclui automaticamente os itens do banco se a data de lançamento já chegou
        Upcoming::where('release_date', '<=', now()->toDateString())->delete();

        return Upcoming::orderBy('release_date', 'asc')->limit($limit)->get();
    }

    private function resolveNetworksList($limit)
    {
        return Network::orderBy('name')->limit($limit)->get();
    }

    private function resolveEvents($limit)
    {
        $now = now()->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s');

        return Event::with(['homeTeam', 'awayTeam'])
             ->visible()
             ->orderByRaw("CASE WHEN ? >= start_time AND ? <= end_time THEN 0 ELSE 1 END ASC", [$now, $now])
             ->orderBy('start_time')
             ->limit($limit)
             ->get()
             ->map(function ($event) {
                 $event->home_team_image = $event->homeTeam?->image_url;
                 $event->away_team_image = $event->awayTeam?->image_url;
                 // Unset relationships to keep the JSON payload clean
                 unset($event->homeTeam, $event->awayTeam);
                 return $event;
             });
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

    private function isDedicatedCategory(): bool
    {
        if (!$this->content_category_id) return false;
        return $this->category && $this->category->has_dedicated_content;
    }

    private function applyCategoryFilter($query)
    {
        if (!$this->content_category_id) {
            // Home: sem filtro, mostra tudo (geral)
            return $query;
        }

        if ($this->isDedicatedCategory()) {
            // Animes/Doramas: mostrar só conteúdo dessa categoria
            $query->where('content_category_id', $this->content_category_id);
        } else {
            // Filmes/outras: mostrar só conteúdo geral (sem categoria)
            $query->whereNull('content_category_id');
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
        $results = collect();

        // 1. Movies Trending
        if (in_array($this->content_type, ['movie', 'both'])) {
            $query = ContentView::select('content_id')
                ->selectRaw('COUNT(*) as views_count')
                ->join('movies', 'content_views.content_id', '=', 'movies.id')
                ->where('content_views.content_type', 'movie')
                ->groupBy('content_id');

            if ($this->content_category_id) {
                if ($this->isDedicatedCategory()) {
                    $query->where('movies.content_category_id', $this->content_category_id);
                } else {
                    $query->whereNull('movies.content_category_id');
                }
            }

            if ($period === 'today') {
                $query->whereDate('viewed_at', today());
            } elseif ($period === 'week') {
                $query->where('viewed_at', '>=', now()->subWeek());
            }

            $trendingMovies = $query->orderByDesc('views_count')->limit($limit)->get();
            
            foreach ($trendingMovies as $item) {
                $movie = Movie::find($item->content_id);
                if ($movie) {
                    $movie->views_count = $item->views_count;
                    $results->push($movie);
                }
            }
        }

        // 2. Series Trending
        if (in_array($this->content_type, ['series', 'both'])) {
            $query = ContentView::select('content_id')
                ->selectRaw('COUNT(*) as views_count')
                ->join('series', 'content_views.content_id', '=', 'series.id')
                ->where('content_views.content_type', 'series')
                ->groupBy('content_id');

            if ($this->content_category_id) {
                if ($this->isDedicatedCategory()) {
                    $query->where('series.content_category_id', $this->content_category_id);
                } else {
                    $query->whereNull('series.content_category_id');
                }
            }

            if ($period === 'today') {
                $query->whereDate('viewed_at', today());
            } elseif ($period === 'week') {
                $query->where('viewed_at', '>=', now()->subWeek());
            }

            $trendingSeries = $query->orderByDesc('views_count')->limit($limit)->get();
            
            foreach ($trendingSeries as $item) {
                $serie = Serie::find($item->content_id);
                if ($serie) {
                    $serie->views_count = $item->views_count;
                    $results->push($serie);
                }
            }
        }

        return $results->sortByDesc('views_count')->take($limit)->values();
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
