<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\ContentCategory;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    public function show($id, Request $request)
    {
        $section = HomeSection::findOrFail($id);
        return $this->respond($section, $request);
    }

    public function publicHome(string $slug, Request $request)
    {
        $section = HomeSection::query()
            ->whereNull('content_category_id')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->respond($section, $request);
    }

    public function publicCategory(string $categorySlug, string $slug, Request $request)
    {
        $category = ContentCategory::query()->active()->where('slug', $categorySlug)->firstOrFail();
        $section = HomeSection::query()
            ->where('content_category_id', $category->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->respond($section, $request, $category);
    }

    private function respond(HomeSection $section, Request $request, ?ContentCategory $category = null)
    {

        // Para seções de gênero ou network, também podemos carregar aqui diretamente para o "ver tudo"
        // Ou se preferir redirecionar, mas aqui garantimos uma resposta uniforme para o app.
        $page = (int) $request->get('page', 1);
        $perPage = 20;

        // Resolve os itens com um limite alto para o "Ver Tudo"
        $content = $section->resolveItems(5000); 
        $paginated = $content->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'slug' => $section->slug,
                'type' => $section->type,
                'content_type' => $section->content_type ?? 'both',
                'category' => $category ? ['name' => $category->name, 'slug' => $category->slug] : null,
            ],
            'data' => $paginated->map(function ($item) {
                if ($item instanceof Movie) {
                    return [
                        'id' => $item->id,
                        'type' => 'movie',
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'year' => $item->release_year,
                        'rating' => $item->rating,
                        'poster' => $item->poster_path,
                        'backdrop' => $item->backdrop_path,
                    ];
                }

                if ($item instanceof Serie) {
                    return [
                        'id' => $item->id,
                        'type' => 'series',
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'year' => $item->first_air_year,
                        'rating' => $item->rating,
                        'poster' => $item->poster_path,
                        'backdrop' => $item->backdrop_path,
                    ];
                }

                return $item;
            })->values(),
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $content->count(),
            'last_page' => (int) ceil($content->count() / $perPage)
        ]);
    }
}
