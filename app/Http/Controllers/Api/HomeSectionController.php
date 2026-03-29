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

        // Se for uma seção de "gênero", o conteúdo total é o mesmo que o do gênero
        if ($section->type === 'genre' && $section->genre_id) {
            return redirect()->action([GenreController::class, 'show'], ['idOrSlug' => $section->genre_id]);
        }

        // Se for uma seção de "network", o conteúdo total é o mesmo que o da network
        if ($section->type === 'network' && $section->network_id) {
            return redirect()->action([NetworkController::class, 'show'], ['idOrSlug' => $section->network_id]);
        }

        /*
        =========================
        RESOLVE ITENS (SEM LIMITE)
        =========================
        */

        // Para seções customizadas ou trending, pegamos todos os itens
        // Como o resolveItems() do model usa um $limit fixo, 
        // vamos implementar uma versão que ignore esse limite ou use um valor maior

        $items = $section->resolveItems(); // Por enquanto usamos o padrão do model

        // TODO: Implementar paginação real se necessário no futuro
        // Para agora, retornamos o que o model resolve, mas idealmente 
        // expandiríamos o resolveItems para aceitar parâmetros.

        return response()->json([
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'type' => $section->type,
            ],
            'data' => $items
        ]);
    }
}
