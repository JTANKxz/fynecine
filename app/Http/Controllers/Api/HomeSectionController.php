<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    public function show($id, Request $request)
    {
        $section = HomeSection::findOrFail($id);

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
                'type' => $section->type,
                'content_type' => $section->content_type ?? 'both',
            ],
            'data' => $paginated,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $content->count(),
            'last_page' => (int) ceil($content->count() / $perPage)
        ]);
    }
}
