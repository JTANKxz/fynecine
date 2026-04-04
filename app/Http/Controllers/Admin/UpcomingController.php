<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Upcoming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UpcomingController extends Controller
{
    use \App\Traits\ImportableContent;
    
    public function index()
    {
        $upcomings = Upcoming::orderBy('release_date', 'asc')->paginate(20);
        return view('admin.upcomings.index', compact('upcomings'));
    }

    public function import(Request $request)
    {
        $tmdbId = $request->tmdb_id;
        $type = $request->type; // movie or tv

        // Prevent duplicates
        if (Upcoming::where('tmdb_id', $tmdbId)->exists()) {
             return response()->json(['success' => false, 'error' => 'Já foi importado para Em Breve.']);
        }

        $endpoint = $type == 'tv' ? "tv/{$tmdbId}" : "movie/{$tmdbId}";
        $response = $this->fetchTMDB($endpoint, ['language' => 'pt-BR', 'append_to_response' => 'videos']);

        if (!$response->successful()) {
            return response()->json(['success' => false, 'error' => 'Erro ao buscar no TMDB.']);
        }

        $data = $response->json();
        
        $trailerKey = null;
        if (isset($data['videos']['results'])) {
            foreach ($data['videos']['results'] as $video) {
                if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                    $trailerKey = $video['key'];
                    break;
                }
            }
        }

        $upcoming = Upcoming::create([
            'tmdb_id' => $tmdbId,
            'title' => $data['title'] ?? $data['name'],
            'type' => $type == 'tv' ? 'series' : 'movie',
            'poster_path' => $data['poster_path'] ? "https://image.tmdb.org/t/p/w500{$data['poster_path']}" : null,
            'backdrop_path' => $data['backdrop_path'] ? "https://image.tmdb.org/t/p/original{$data['backdrop_path']}" : null,
            'release_date' => $data['release_date'] ?? $data['first_air_date'] ?? null,
            'trailer_key' => $trailerKey,
        ]);

        return response()->json([
            'success' => true,
            'upcoming' => $upcoming
        ]);
    }

    public function destroy(Upcoming $upcoming)
    {
        $upcoming->delete();
        return back()->with('success', 'Removido dos Lançamentos Em Breve.');
    }
}
