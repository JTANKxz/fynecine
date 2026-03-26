<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\Genre;

class HomeController extends Controller
{
    public function index()
    {

        /*
        ==================
        SLIDERS
        ==================
        */

        $sliders = Slider::where('active', true)
            ->orderBy('position')
            ->get()
            ->map(function ($slider) {

                $content = $slider->content;

                return [
                    'id' => $content->id,
                    'slug' => $content->slug, // 👈 AQUI
                    'type' => $slider->content_type,

                    'title' => $slider->content_type === 'movie'
                        ? $content->title
                        : $content->name,

                    'rating' => $content->rating,

                    'year' => $slider->content_type === 'movie'
                        ? $content->release_year
                        : $content->first_air_year,

                    'poster' => $content->poster_path,
                    'backdrop' => $content->backdrop_path,
                ];
            });

        /*
        ==================
        GENEROS
        ==================
        */

        $genres = Genre::select('id', 'name', 'slug')->get();


        /*
        ==================
        FILMES POPULARES
        ==================
        */

        $popularMovies = Movie::orderBy('rating', 'desc')
            ->limit(15)
            ->get();


        /*
        ==================
        SERIES POPULARES
        ==================
        */

        $popularSeries = Serie::orderBy('rating', 'desc')
            ->limit(15)
            ->get();


        /*
        ==================
        FILMES RECENTES
        ==================
        */

        $latestMovies = Movie::latest()
            ->limit(15)
            ->get();


        return response()->json([

            'sliders' => $sliders,

            'genres' => $genres,

            'sections' => [

                [
                    'title' => 'Filmes Populares',
                    'type' => 'movie',
                    'items' => $popularMovies
                ],

                [
                    'title' => 'Séries Populares',
                    'type' => 'series',
                    'items' => $popularSeries
                ],

                [
                    'title' => 'Filmes Recentes',
                    'type' => 'movie',
                    'items' => $latestMovies
                ]

            ]

        ]);
    }
}