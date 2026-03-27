<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index()
    {
        $requests = ContentRequest::with('user')->latest()->paginate(20);
        return view('admin.requests.index', compact('requests'));
    }

    public function destroy(ContentRequest $request)
    {
        $request->delete();
        return back()->with('success', 'Pedido deletado com sucesso.');
    }

    public function updateStatus(Request $requestData, ContentRequest $request)
    {
        $requestData->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $request->update(['status' => $requestData->status]);
        return back()->with('success', 'Status do pedido alterado.');
    }

    public function autoImport(ContentRequest $request)
    {
        $tmdbController = app(\App\Http\Controllers\Admin\TMDBController::class);
        
        try {
            if ($request->type === 'movie') {
                $response = $tmdbController->importMovie($request->tmdb_id);
            } else {
                // Importa série completa por padrão no pedido
                $response = $tmdbController->importSeries($request->tmdb_id, true);
            }

            // Se o import foi com sucesso ou já existia
            if ($response && $response->getStatusCode() === 200) {
                $request->update(['status' => 'approved']);
                return back()->with('success', "O título '{$request->title}' foi importado automaticamente para o catálogo!");
            }

            return back()->with('error', "Falha ao importar o título do TMDB.");
            
        } catch (\Exception $e) {
            return back()->with('error', "Erro fatal no conversor do TMDB: " . $e->getMessage());
        }
    }
}
