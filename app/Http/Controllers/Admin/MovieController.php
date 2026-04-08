<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MoviePlayLink;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    use \App\Traits\ImportableContent;

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
        $categories = \App\Models\ContentCategory::where('has_dedicated_content', true)->get();
        return view('admin.movies.index', compact('movies', 'categories'));
    }

    public function bulkImport()
    {
        return view('admin.movies.bulk');
    }

    public function getBulkIds()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get('https://embedplayapi.site/api/all-ids?type=movie');
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Não foi possível buscar os IDs da API externa.'], 500);
            }

            $data = $response->json();
            $movies = $data['results']['movies'] ?? [];

            return response()->json([
                'success' => true,
                'movies' => $movies
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processImport(Request $request)
    {
        $tmdbId = $request->tmdb_id;

        if (!$tmdbId) {
            return response()->json(['success' => false, 'error' => 'TMDB ID não fornecido.']);
        }

        // Verificar se já existe
        if (Movie::where('tmdb_id', $tmdbId)->exists()) {
            return response()->json([
                'success' => true, 
                'status' => 'exists',
                'message' => "Filme (TMDB: $tmdbId) já existe no banco. Pulando..."
            ]);
        }

        $result = $this->performMovieImport($tmdbId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'status' => 'imported',
                'movie' => $result['movie']
            ]);
        }

        return response()->json([
            'success' => false,
            'status' => 'error',
            'error' => $result['error']
        ]);
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
            'url' => 'required|string',
            'type' => 'required|in:embed,mp4,m3u8,mkv,custom,private',
            'player_sub' => 'required|in:free,premium',
            'link_path' => 'nullable|string',
            'expiration_hours' => 'nullable|integer',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
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
            'type' => 'required|in:embed,mp4,m3u8,mkv,custom,private',
            'player_sub' => 'required|in:free,premium',
            'link_path' => 'nullable|string',
            'expiration_hours' => 'nullable|integer',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
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

    public function updateCategory(Request $request, Movie $movie)
    {
        $movie->update(['content_category_id' => $request->content_category_id ?: null]);
        return back()->with('success', 'Categoria atualizada com sucesso!');
    }

    public function updateTag(Request $request, Movie $movie)
    {
        $request->validate([
            'tag_text' => 'nullable|string|max:50',
            'tag_expires_at' => 'nullable|date'
        ]);

        $movie->update([
            'tag_text' => $request->tag_text,
            'tag_expires_at' => $request->tag_expires_at
        ]);

        return back()->with('success', 'Tag atualizada com sucesso!');
    }

    public function updateSettings(Request $request, Movie $movie)
    {
        $movie->update([
            'use_autoembed' => $request->has('use_autoembed')
        ]);

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}