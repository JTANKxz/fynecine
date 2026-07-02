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
        $sliders = Slider::with('movie', 'serie')->orderBy('position')->get();
        $sections = HomeSection::where('is_active', true)->orderBy('order')->get();
        
        return view('frontend.index', compact('sliders', 'sections'));
    }

    public function movie($slug)
    {
        $movie = Movie::where('slug', $slug)->firstOrFail();
        $related = Movie::whereHas('genres', function($q) use ($movie) {
            $q->whereIn('genres.id', $movie->genres->pluck('id'));
        })->where('id', '!=', $movie->id)->limit(12)->get();

        return view('frontend.movie', compact('movie', 'related'));
    }

    public function serie($slug)
    {
        $serie = Serie::where('slug', $slug)->with('seasons.episodes')->firstOrFail();
        $related = Serie::whereHas('genres', function($q) use ($serie) {
            $q->whereIn('genres.id', $serie->genres->pluck('id'));
        })->where('id', '!=', $serie->id)->limit(12)->get();

        return view('frontend.serie', compact('serie', 'related'));
    }

    public function episode($serieSlug, $seasonNumber, $episodeNumber)
    {
        $serie = Serie::where('slug', $serieSlug)->firstOrFail();
        $season = Season::where('series_id', $serie->id)->where('season_number', $seasonNumber)->firstOrFail();
        $episode = Episode::where('season_id', $season->id)->where('episode_number', $episodeNumber)->firstOrFail();
        
        return view('frontend.episode', compact('serie', 'season', 'episode'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $categoria = $request->input('categoria', 'todos');
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
            if ($ano) $mQuery->where('release_year', $ano);
            if ($avaliacao) $mQuery->where('rating', '>=', $avaliacao);
            if ($duracao) {
                if ($duracao == '90') $mQuery->where('runtime', '<=', 90);
                if ($duracao == '120') $mQuery->whereBetween('runtime', [90, 120]);
                if ($duracao == '150') $mQuery->where('runtime', '>', 120);
            }
            if ($categoria === 'animes') {
                $mQuery->where('content_type', 'anime');
            }
            $movies = $mQuery->latest()->get();
        }

        // Query Series
        if ($categoria === 'todos' || $categoria === 'series' || $categoria === 'animes') {
            $sQuery = Serie::query();
            if ($query) $sQuery->where('name', 'LIKE', "%{$query}%");
            if ($ano) $sQuery->where('first_air_year', $ano);
            if ($avaliacao) $sQuery->where('rating', '>=', $avaliacao);
            // duracao mostly applies to movies, but we can ignore for series or filter by episode length if it existed.
            if ($categoria === 'animes') {
                $sQuery->where('content_type', 'anime');
            }
            $series = $sQuery->latest()->get();
        }

        // Combine and Paginate manually
        $all = $movies->concat($series)->sortByDesc('created_at')->values();
        
        $page = $request->input('page', 1);
        $total = $all->count();
        $results = $all->slice(($page - 1) * $limit, $limit)->values();

        $hasMore = ($page * $limit) < $total;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.partials.catalog_cards', compact('results'))->render(),
                'hasMore' => $hasMore
            ]);
        }

        return view('frontend.search', compact('results', 'query', 'categoria', 'ano', 'avaliacao', 'duracao', 'hasMore'));
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
        // Assuming network_content table exists based on HomeSection logic
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
}
