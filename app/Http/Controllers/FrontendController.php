<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Serie;
use App\Models\Slider;
use App\Models\HomeSection;
use App\Models\Genre;
use App\Models\Network;
use App\Models\Episode;
use App\Models\Season;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        // Only sliders without a specific content_category (= home/general)
        $sliders = Slider::with(['movie', 'serie'])
            ->whereNull('content_category_id')
            ->where(function($q) {
                $q->where('active', true)->orWhereNull('active');
            })
            ->orderBy('position')
            ->get()
            ->filter(fn($s) => $s->movie || $s->serie)
            ->values();

        // Home sections: active, ordered, with genre/category relationships
        $sections = HomeSection::where('is_active', true)
            ->with(['genre', 'category'])
            ->orderBy('order')
            ->get();

        return view('frontend.home', compact('sliders', 'sections'));
    }

    public function movie($identifier)
    {
        $movie = Movie::with('playLinks', 'cast')
            ->where('slug', $identifier)
            ->orWhere('tmdb_id', $identifier)
            ->firstOrFail();
        $related = Movie::whereHas('genres', function($q) use ($movie) {
            $q->whereIn('genres.id', $movie->genres->pluck('id'));
        })->where('id', '!=', $movie->id)->limit(12)->get();

        return view('frontend.movie', compact('movie', 'related'));
    }

    public function serie($identifier)
    {
        $serie = Serie::with(['seasons.episodes.links', 'cast'])
            ->where('slug', $identifier)
            ->orWhere('tmdb_id', $identifier)
            ->firstOrFail();
        $related = Serie::whereHas('genres', function($q) use ($serie) {
            $q->whereIn('genres.id', $serie->genres->pluck('id'));
        })->where('id', '!=', $serie->id)->limit(12)->get();

        return view('frontend.serie', compact('serie', 'related'));
    }

    public function player(\Illuminate\Http\Request $request, $slug)
    {
        $url = $request->query('url');
        
        if (!$url) {
            abort(404, 'URL do player não fornecida.');
        }

        // Tenta decodificar a URL em base64. Se não for base64 válido, assume que é a própria URL.
        $decodedUrl = base64_decode($url, true);
        if ($decodedUrl !== false && filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
            $url = $decodedUrl;
        }

        return view('frontend.player', compact('url', 'slug'));
    }

    public function episode($serieSlug, $seasonNumber, $episodeNumber)
    {
        $serie = Serie::where('slug', $serieSlug)->firstOrFail();
        $season = Season::where('series_id', $serie->id)->where('season_number', $seasonNumber)->firstOrFail();
        $episode = Episode::with('links')->where('season_id', $season->id)->where('episode_number', $episodeNumber)->firstOrFail();
        
        $otherEpisodes = Episode::where('season_id', $season->id)->orderBy('episode_number', 'asc')->get();
        $prevEp = $otherEpisodes->where('episode_number', '<', $episodeNumber)->last();
        $nextEp = $otherEpisodes->where('episode_number', '>', $episodeNumber)->first();
        
        return view('frontend.episode', compact('serie', 'season', 'episode', 'otherEpisodes', 'prevEp', 'nextEp'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $categoria = $request->input('categoria', 'todos');
        $genero = $request->input('genero', 'todos');
        $ano = $request->input('ano', '');
        $avaliacao = $request->input('avaliacao', '');
        $duracao = $request->input('duracao', '');
        
        $limit = 24;
        
        $movies = collect();
        $series = collect();

        // Query Movies
        if ($categoria === 'todos' || $categoria === 'filmes' || $categoria === 'animes') {
            $mQuery = Movie::query();
            if ($query) $mQuery->where('title', 'LIKE', "%{$query}%");
            if ($ano && $ano !== 'todos') $mQuery->where('release_year', $ano);
            if ($avaliacao && $avaliacao !== '0') $mQuery->where('rating', '>=', $avaliacao);
            if ($duracao && $duracao !== '0') $mQuery->where('runtime', '<=', $duracao);
            if ($genero && $genero !== 'todos') {
                $mQuery->whereHas('genres', function($q) use ($genero) {
                    $q->where('slug', strtolower($genero));
                });
            }
            if ($categoria === 'animes') {
                $mQuery->whereHas('contentCategory', function($q) {
                    $q->where('slug', 'like', '%anime%');
                });
            } elseif ($categoria === 'filmes') {
                $mQuery->whereNull('content_category_id');
            }
            $movies = $mQuery->latest()->get();
        }

        // Query Series
        if ($categoria === 'todos' || $categoria === 'series' || $categoria === 'animes') {
            $sQuery = Serie::query();
            if ($query) $sQuery->where('name', 'LIKE', "%{$query}%");
            if ($ano && $ano !== 'todos') $sQuery->where('first_air_year', $ano);
            if ($avaliacao && $avaliacao !== '0') $sQuery->where('rating', '>=', $avaliacao);
            if ($genero && $genero !== 'todos') {
                $sQuery->whereHas('genres', function($q) use ($genero) {
                    $q->where('slug', strtolower($genero));
                });
            }
            // duracao mostly applies to movies, but we can ignore for series or filter by episode length if it existed.
            if ($categoria === 'animes') {
                $sQuery->whereHas('contentCategory', function($q) {
                    $q->where('slug', 'like', '%anime%');
                });
            } elseif ($categoria === 'series') {
                $sQuery->whereNull('content_category_id');
            }
            $series = $sQuery->latest()->get();
        }

        // Combine and Paginate manually
        $all = $movies->concat($series)->sortByDesc('created_at')->values();
        
        $page = $request->input('page', 1);
        $total = $all->count();
        $items = $all->slice(($page - 1) * $limit, $limit)->values();

        $results = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $limit,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('frontend.search', compact('results', 'query', 'categoria', 'genero', 'ano', 'avaliacao', 'duracao'));
    }

    public function genre($slug)
    {
        $genre = Genre::where('slug', $slug)->firstOrFail();
        $movies = Movie::whereHas('genres', fn($q) => $q->where('genres.id', $genre->id))->latest()->paginate(24);
        $series = Serie::whereHas('genres', fn($q) => $q->where('genres.id', $genre->id))->latest()->paginate(24);
        
        return view('frontend.browse', [
            'title' => "Gênero: {$genre->name}",
            'movies' => $movies,
            'series' => $series,
            'description' => "Assistir filmes e séries de {$genre->name} online grátis."
        ]);
    }

    public function network($slug)
    {
        $network = Network::where('slug', $slug)->firstOrFail();
        $movieIds = \DB::table('network_content')->where('network_id', $network->id)->where('content_type', 'movie')->pluck('content_id');
        $serieIds = \DB::table('network_content')->where('network_id', $network->id)->where('content_type', 'series')->pluck('content_id');
        
        $movies = Movie::whereIn('id', $movieIds)->latest()->paginate(24);
        $series = Serie::whereIn('id', $serieIds)->latest()->paginate(24);

        return view('frontend.browse', [
            'title' => "Rede: {$network->name}",
            'movies' => $movies,
            'series' => $series,
            'description' => "Assistir conteúdos da {$network->name} online grátis."
        ]);
    }

    public function appDownload()
    {
        $settings = \App\Models\AppConfig::getSettings();
        return view('frontend.app-download', compact('settings'));
    }
}
