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
        $items = $section->resolveItems(100); 

        return response()->json([
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'type' => $section->type,
                'content_type' => $section->content_type ?? 'both',
            ],
            'data' => $items
        ]);
    }
}
