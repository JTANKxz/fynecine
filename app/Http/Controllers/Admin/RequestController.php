<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\FcmDevice;
use App\Services\FcmService;
use Illuminate\Http\Request;
 
class RequestController extends Controller
{
    protected $fcmService;
 
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
 
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
 
    public function respond(Request $requestData, ContentRequest $request)
    {
        $requestData->validate([
            'message' => 'required|string',
            'status' => 'required|in:approved,rejected',
            'send_push' => 'boolean',
            'send_in_app' => 'boolean',
            'action_type' => 'nullable|string',
            'action_value' => 'nullable|string',
        ]);
 
        $request->update(['status' => $requestData->status]);
 
        $user = $request->user;
        $title = "Seu pedido de '{$request->title}' foi " . ($requestData->status === 'approved' ? 'Aprovado!' : 'Rejeitado');
        $content = $requestData->message;
        
        $isInApp = $requestData->boolean('send_in_app', true);
        $isPush = $requestData->boolean('send_push', false);
 
        if ($isInApp) {
            Notification::create([
                'title' => $title,
                'content' => $content,
                'action_type' => $requestData->action_type ?? 'none',
                'action_value' => $requestData->action_value,
                'user_id' => $user->id,
                'segment' => 'individual',
                'is_in_app' => true,
                'push_status' => $isPush ? 'pending' : 'none',
            ]);
        }
 
        if ($isPush) {
            $tokens = FcmDevice::where('user_id', $user->id)->pluck('device_token')->toArray();
            if (!empty($tokens)) {
                $this->fcmService->sendPush($tokens, [
                    'title' => $title,
                    'body' => $content,
                    'action_type' => $requestData->action_type ?? 'none',
                    'action_value' => $requestData->action_value,
                ]);
            }
        }
 
        return back()->with('success', 'Pedido respondido e usuário notificado!');
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
