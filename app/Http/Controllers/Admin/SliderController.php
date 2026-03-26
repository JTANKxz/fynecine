<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class SliderController extends Controller
{

    public function index()
    {
        $sliders = Slider::orderBy('position')
            ->paginate(10);

        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'content_id' => 'required|integer',
            'content_type' => 'required|in:movie,series',
            'position' => 'nullable|integer',
        ]);

        Slider::create([
            'content_id' => $validated['content_id'],
            'content_type' => $validated['content_type'],
            'position' => $validated['position'] ?? 0,
            'active' => true
        ]);

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Item adicionado ao slider!');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider removido!');
    }

    /*
    ===========================
    BUSCA FILMES E SÉRIES
    ===========================
    */

    public function search(Request $request)
    {

        $search = $request->input('query');

        $movies = Movie::where('title', 'like', "%{$search}%")
            ->limit(5)
            ->get()
            ->map(function ($movie) {
                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'type' => 'movie',
                    'year' => $movie->release_year,
                    'rating' => $movie->rating,
                    'backdrop' => $movie->backdrop_path
                ];
            });

        $series = Serie::where('name', 'like', "%{$search}%")
            ->limit(5)
            ->get()
            ->map(function ($serie) {
                return [
                    'id' => $serie->id,
                    'title' => $serie->name,
                    'type' => 'series',
                    'year' => $serie->first_air_year,
                    'rating' => $serie->rating,
                    'backdrop' => $serie->backdrop_path
                ];
            });

        $results = $movies->concat($series)->values();

        return response()->json($results);
    }
}