<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Slider;

class PublicHomeController extends Controller
{
    public function index()
    {
        // Sliders sem categoria específica (= home/geral)
        $sliders = Slider::with(['movie', 'serie'])
            ->whereNull('content_category_id')
            ->where(function ($q) {
                $q->where('active', true)->orWhereNull('active');
            })
            ->orderBy('position')
            ->get()
            ->filter(fn($s) => $s->movie || $s->serie)
            ->values();

        // Seções ativas APENAS da página de início (sem categoria específica)
        // Exclui redes e chegando em breve
        $sections = HomeSection::where('is_active', true)
            ->whereNull('content_category_id')
            ->whereNotIn('type', ['networks', 'upcoming'])
            ->with(['genre', 'category'])
            ->orderBy('order')
            ->get();

        return view('frontend.home', compact('sliders', 'sections'));
    }
}
