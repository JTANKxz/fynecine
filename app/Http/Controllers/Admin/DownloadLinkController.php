<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieDownloadLink;
use App\Models\Serie;
use App\Models\Episode;
use App\Models\EpisodeDownloadLink;
use Illuminate\Http\Request;

class DownloadLinkController extends Controller
{
    // =============================================
    // FILMES
    // =============================================

    public function movies(Request $request)
    {
        $query = Movie::with('downloadLinks');

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('tmdb_id', $search);
        }

        $movies = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.downloads.movies', compact('movies'));
    }

    public function storeMovieDownload(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'url'          => 'required|string',
            'quality'      => 'nullable|string|max:50',
            'size'         => 'nullable|string|max:50',
            'type'         => 'required|in:direct,external',
            'download_sub' => 'required|in:free,premium',
            'order'        => 'nullable|integer',
        ]);

        $movie->downloadLinks()->create($validated);

        return back()->with('success', 'Link de download adicionado com sucesso!');
    }

    public function updateMovieDownload(Request $request, MovieDownloadLink $link)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'url'          => 'required|string',
            'quality'      => 'nullable|string|max:50',
            'size'         => 'nullable|string|max:50',
            'type'         => 'required|in:direct,external',
            'download_sub' => 'required|in:free,premium',
            'order'        => 'nullable|integer',
        ]);

        $link->update($validated);

        return back()->with('success', 'Link de download atualizado!');
    }

    public function destroyMovieDownload(MovieDownloadLink $link)
    {
        $link->delete();
        return back()->with('success', 'Link de download removido.');
    }

    // =============================================
    // SÉRIES (listagem)
    // =============================================

    public function series(Request $request)
    {
        $query = Serie::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('tmdb_id', $search);
        }

        $series = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.downloads.series', compact('series'));
    }

    public function serieManage(Serie $serie)
    {
        $serie->load('seasons.episodes.downloadLinks');
        return view('admin.downloads.serie_manage', compact('serie'));
    }

    // =============================================
    // EPISÓDIOS
    // =============================================

    public function storeEpisodeDownload(Request $request, Episode $episode)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'url'          => 'required|string',
            'quality'      => 'nullable|string|max:50',
            'size'         => 'nullable|string|max:50',
            'type'         => 'required|in:direct,external',
            'download_sub' => 'required|in:free,premium',
            'order'        => 'nullable|integer',
        ]);

        $episode->downloadLinks()->create($validated);

        return back()->with('success', 'Link de download adicionado ao episódio!');
    }

    public function updateEpisodeDownload(Request $request, EpisodeDownloadLink $link)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'url'          => 'required|string',
            'quality'      => 'nullable|string|max:50',
            'size'         => 'nullable|string|max:50',
            'type'         => 'required|in:direct,external',
            'download_sub' => 'required|in:free,premium',
            'order'        => 'nullable|integer',
        ]);

        $link->update($validated);

        return back()->with('success', 'Link de download do episódio atualizado!');
    }

    public function destroyEpisodeDownload(EpisodeDownloadLink $link)
    {
        $link->delete();
        return back()->with('success', 'Link de download do episódio removido.');
    }
}
