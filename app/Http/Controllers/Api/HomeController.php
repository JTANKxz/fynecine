<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Genre;
use App\Models\HomeSection;

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

                if (!$content) return null;

                return [
                    'id' => $content->id,
                    'slug' => $content->slug,
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
            })->filter()->values();

        /*
        ==================
        GENEROS
        ==================
        */
        $genres = Genre::select('id', 'name', 'slug')->get();

        /*
        ==================
        SEÇÕES DINÂMICAS
        ==================
        */
        $sections = HomeSection::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($section) {
                return [
                    'title' => $section->title,
                    'type' => $section->content_type, // 'movie', 'series', 'both'
                    'items' => $section->resolveItems()
                ];
            });

        return response()->json([
            'sliders' => $sliders,
            'genres' => $genres,
            'sections' => $sections
        ]);
    }
}