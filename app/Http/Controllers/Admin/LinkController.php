<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MoviePlayLink;
use App\Models\Serie;
use App\Models\Episode;
use App\Models\EpisodeLink;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function movies(Request $request)
    {
        $query = Movie::with('playLinks');

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('tmdb_id', $search);
        }

        $movies = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.links.movies', compact('movies'));
    }

    public function series(Request $request)
    {
        $query = Serie::with('seasons.episodes.links');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('tmdb_id', $search);
        }

        $series = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.links.series', compact('series'));
    }

    public function storeMovieLink(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'type' => 'required|in:embed,mp4,m3u8,mkv,custom,private',
            'player_sub' => 'required|in:free,premium',
            'quality' => 'nullable|string',
            'order' => 'nullable|integer',
            'link_path' => 'nullable|string|max:255',
            'expiration_hours' => 'nullable|integer|min:1',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
        ]);

        $movie->playLinks()->create($validated);

        return back()->with('success', 'Link adicionado com sucesso!');
    }

    public function updateMovieLink(Request $request, MoviePlayLink $link)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'type' => 'required|in:embed,mp4,m3u8,mkv,custom,private',
            'player_sub' => 'required|in:free,premium',
            'quality' => 'nullable|string',
            'order' => 'nullable|integer',
            'link_path' => 'nullable|string|max:255',
            'expiration_hours' => 'nullable|integer|min:1',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
        ]);

        $link->update($validated);

        return back()->with('success', 'Link atualizado!');
    }

    public function destroyMovieLink(MoviePlayLink $link)
    {
        $link->delete();
        return back()->with('success', 'Link removido.');
    }

    public function serieManage(Serie $serie)
    {
        $serie->load('seasons.episodes.links');
        return view('admin.links.serie_manage', compact('serie'));
    }

    public function storeEpisodeLink(Request $request, Episode $episode)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'type' => 'required|string',
            'player_sub' => 'required|in:free,premium',
            'quality' => 'nullable|string',
            'order' => 'nullable|integer',
            'skip_intro_start' => 'nullable|integer',
            'skip_intro_end' => 'nullable|integer',
            'skip_ending_start' => 'nullable|integer',
            'skip_ending_end' => 'nullable|integer',
            'link_path' => 'nullable|string|max:255',
            'expiration_hours' => 'nullable|integer|min:1',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
        ]);

        $episode->links()->create($validated);

        return back()->with('success', 'Link adicionado ao episódio!');
    }

    public function updateEpisodeLink(Request $request, EpisodeLink $link)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'type' => 'required|string',
            'player_sub' => 'required|in:free,premium',
            'quality' => 'nullable|string',
            'order' => 'nullable|integer',
            'skip_intro_start' => 'nullable|integer',
            'skip_intro_end' => 'nullable|integer',
            'skip_ending_start' => 'nullable|integer',
            'skip_ending_end' => 'nullable|integer',
            'link_path' => 'nullable|string|max:255',
            'expiration_hours' => 'nullable|integer|min:1',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
        ]);

        $link->update($validated);

        return back()->with('success', 'Link do episódio atualizado!');
    }

    public function destroyEpisodeLink(EpisodeLink $link)
    {
        $link->delete();
        return back()->with('success', 'Link do episódio removido.');
    }
}
