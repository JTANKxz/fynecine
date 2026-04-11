<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\Slider;
use App\Models\Genre;

class PublicHomeController extends Controller
{
    public function index()
    {
        $sliders = Slider::with('movie', 'serie')
            ->orderBy('position')
            ->get();

        $latestMovies = Movie::latest()
            ->limit(20)
            ->get();

        $latestSeries = Serie::latest()
            ->limit(20)
            ->get();

        $genres = Genre::orderBy('name')
            ->get();

        return view('frontend.home', compact('sliders', 'latestMovies', 'latestSeries', 'genres'));
    }
}
