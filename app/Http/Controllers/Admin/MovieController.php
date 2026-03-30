<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MoviePlayLink;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        // Inicia query
        $query = Movie::query();

        // Pesquisa por título ou ano
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('release_year', 'like', "%{$search}%");
        }

        // Paginação: 10 filmes por página, ordenados por ID desc
        $movies = $query->orderBy('id', 'desc')->paginate(10);

        // Mantém o termo de pesquisa na paginação
        if ($request->has('search')) {
            $movies->appends(['search' => $request->search]);
        }

        // Retorna view com os filmes
        return view('admin.movies.index', compact('movies'));
    }

    public function links(Movie $movie)
    {
        $links = $movie->playLinks()->orderBy('order')->get();

        return view('admin.movies.links.index', compact('movie', 'links'));
    }

    public function createLink(Movie $movie)
    {
        return view('admin.movies.links.create', compact('movie'));
    }

    public function storeLink(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quality' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'url' => 'required|url',
            'type' => 'required|in:embed,mp4,m3u8,mkv,custom',
            'player_sub' => 'required|in:free,premium',
        ]);

        $validated['movie_id'] = $movie->id;

        MoviePlayLink::create($validated);

        return redirect()
            ->route('admin.movies.links', $movie->id)->with('success', 'Player adicionado com sucesso!');
    }

    public function editLink(MoviePlayLink $link)
    {
        return view('admin.movies.links.edit', compact('link'));
    }

    public function updateLink(Request $request, MoviePlayLink $link)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'quality' => 'nullable|string',
            'order' => 'nullable|integer',
            'url' => 'required|string',
            'type' => 'required|in:embed,mp4,m3u8,mkv,custom',
            'player_sub' => 'required|in:free,premium',
        ]);

        $link->update($data);

        return redirect()
            ->route('admin.movies.links', $link->movie_id)
            ->with('success', 'Link atualizado');
    }

    public function deleteLink(MoviePlayLink $link)
    {
        $link->delete();

        return back()->with('success', 'Player removido!');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.movies.index')
            ->with('success', 'Filme deletado com sucesso!');
    }
}