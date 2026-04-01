<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\FcmDevice;
use App\Models\Movie;
use App\Models\Serie;
use App\Services\FcmService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request)
    {
        $query = Notification::query()->orderBy('created_at', 'desc');

        $type = $request->query('type');
        if ($type === 'push') {
            $query->whereNotNull('push_status')->where('push_status', '!=', 'none');
        } elseif ($type === 'in_app') {
            $query->where('is_in_app', true);
        }

        $notifications = $query->paginate(20)->withQueryString();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Enviar notificação para usuário específico
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'send_push' => 'boolean',
            'send_in_app' => 'boolean',
        ]);

        $user = User::findOrFail($request->user_id);
        $isInApp = $request->boolean('send_in_app', true);
        $isPush = $request->boolean('send_push', false);

        // Salvar notificação se for in-app
        if ($isInApp) {
            $notification = Notification::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'image_url' => null,
                'big_picture_url' => null,
                'action_type' => 'none',
                'action_value' => null,
                'is_global' => false,
                'user_id' => $user->id,
                'segment' => 'individual',
                'is_in_app' => true,
                'push_status' => $isPush ? 'pending' : 'none',
            ]);
        } else {
            // Push puro sem histórico
            $notification = new Notification([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'segment' => 'individual',
                'user_id' => $user->id,
                'is_in_app' => false,
            ]);
        }

        // Enviar push se solicitado
        if ($isPush) {
            $this->sendPushToUser($notification, $user);
        }

        $message = $isPush && !$isInApp ? 'Push enviado com sucesso!' : 'Notificação enviada com sucesso!';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Enviar notificação para conteúdo (filme/série)
     */
    public function sendToContent(Request $request)
    {
        $request->validate([
            'content_type' => 'required|in:movie,serie',
            'content_id' => 'required|integer',
            'segment' => 'required|in:all,premium,basic,free,guest,expired',
            'send_push' => 'boolean',
            'send_in_app' => 'boolean',
        ]);

        $contentType = $request->input('content_type');
        $contentId = $request->input('content_id');

        // Buscar conteúdo
        if ($contentType === 'movie') {
            $content = Movie::findOrFail($contentId);
            $title = $content->title;
            $image = $content->poster_path;
            $actionType = 'movie';
        } else {
            $content = Serie::findOrFail($contentId);
            $title = $content->name;
            $image = $content->poster_path;
            $actionType = 'series';
        }

        $isInApp = $request->boolean('send_in_app', true);
        $isPush = $request->boolean('send_push', false);

        // Criar notificação
        $notificationData = [
            'title' => "Novo $contentType disponível: $title",
            'content' => "Assista agora!",
            'image_url' => $image,
            'big_picture_url' => $image,
            'action_type' => $actionType,
            'action_value' => (string)$contentId,
            'is_global' => $request->input('segment') === 'all',
            'user_id' => null,
            'segment' => $request->input('segment'),
            'is_in_app' => $isInApp,
            'push_status' => $isPush ? 'pending' : 'none',
        ];

        if ($isInApp) {
            $notification = Notification::create($notificationData);
        } else {
            // Push puro
            $notification = new Notification($notificationData);
        }

        // Enviar push se solicitado
        if ($isPush) {
            $this->sendPush($notification);
        }

        $message = $isPush && !$isInApp ? 'Push enviado com sucesso!' : 'Notificação enviada com sucesso!';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Enviar push para usuário específico
     */
    protected function sendPushToUser(Notification $notification, User $user)
    {
        $devices = FcmDevice::where('user_id', $user->id)->pluck('device_token')->toArray();

        if (count($devices) > 0) {
            $data = [
                'title' => $notification->title,
                'body' => $notification->content,
                'image_url' => $notification->image_url,
                'action_type' => $notification->action_type,
                'action_value' => $notification->action_value,
            ];

            $this->fcmService->sendPush($devices, $data);

            if ($notification->id) {
                $notification->update(['push_status' => 'sent']);
            }
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'action_type' => 'required|in:none,url,movie,series,plans',
            'segment' => 'required|in:all,premium,basic,free,guest,individual,expired',
            'user_id' => 'required_if:segment,individual|nullable|exists:users,id',
            'image_url' => 'nullable|url',
            'big_picture_url' => 'nullable|url',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $isSendPush = $request->boolean('send_push', false);
        $isInApp = $request->boolean('is_in_app', true);

        // Só salva no histórico se for in-app
        // Push puro apenas envia sem registrar no histórico
        if (!$isSendPush || $isInApp) {
            $notification = Notification::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'image_url' => $request->input('image_url'),
                'big_picture_url' => $request->input('big_picture_url'),
                'action_type' => $request->input('action_type'),
                'action_value' => $request->input('action_value'),
                'is_global' => $request->input('segment') === 'all',  // Só é global se for 'all'
                'user_id' => $request->input('segment') === 'individual' ? $request->input('user_id') : null,
                'segment' => $request->input('segment'),
                'expires_at' => $request->input('expires_at'),
                'is_in_app' => $isInApp,
                'push_status' => $isSendPush ? 'pending' : 'none',
            ]);
        } else {
            // Push puro: criar objeto temporário apenas para enviar push
            $notification = new Notification([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'image_url' => $request->input('image_url'),
                'big_picture_url' => $request->input('big_picture_url'),
                'action_type' => $request->input('action_type'),
                'action_value' => $request->input('action_value'),
                'segment' => $request->input('segment'),
                'is_in_app' => false,
            ]);
        }

        // Enviar push se solicitado
        if ($isSendPush) {
            $this->sendPush($notification);
        }

        $message = $isSendPush && !$isInApp ? 'Push enviado com sucesso!' : 'Notificação processada com sucesso!';
        return redirect()->route('admin.notifications.index')->with('success', $message);
    }

    protected function sendPush(Notification $notification)
    {
        $query = FcmDevice::query();

        // Segmentation logic
        switch ($notification->segment) {
            case 'premium':
            case 'basic':
            case 'free':
                $query->whereHas('user', function($q) use ($notification) {
                    $q->where('plan_type', $notification->segment);
                    // Filter by active status for specific plan types
                    $q->where(function($sq) {
                        $sq->whereNull('plan_expires_at')
                           ->orWhere('plan_expires_at', '>', now());
                    });
                });
                break;
            case 'expired':
                $query->whereHas('user', function($q) {
                    $q->whereNotNull('plan_expires_at')
                       ->where('plan_expires_at', '<', now());
                });
                break;
            case 'guest':
                $query->whereNull('user_id');
                break;
            case 'individual':
                $query->where('user_id', $notification->user_id);
                break;
            case 'all':
            default:
                // No extra filter
                break;
        }

        $tokens = $query->pluck('device_token')->toArray();

        if (count($tokens) > 0) {
            $data = [
                'title' => $notification->title,
                'body' => $notification->content,
                'image_url' => $notification->image_url,
                'big_picture_url' => $notification->big_picture_url,
                'action_type' => $notification->action_type,
                'action_value' => $notification->action_value,
            ];

            $results = $this->fcmService->sendPush($tokens, $data);
            
            $notification->update(['push_status' => 'sent']);
            return $results;
        }

        return [];
    }

    public function searchContent(Request $request)
    {
        $search = $request->query('q');
        $type = $request->query('type');

        if (strlen($search) < 2) return response()->json([]);

        if ($type === 'movie') {
            $results = Movie::where('title', 'like', "%{$search}%")->limit(10)->get(['id', 'title', 'poster']);
        } else {
            $results = Serie::where('title', 'like', "%{$search}%")->limit(10)->get(['id', 'title', 'poster']);
        }

        return response()->json($results);
    }

    public function searchUser(Request $request)
    {
        $search = $request->query('q');
        if (strlen($search) < 2) return response()->json([]);

        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'avatar']);

        return response()->json($users);
    }

    public function destroy($notification)
    {
        // Buscar a notificação diretamente
        $notif = Notification::findOrFail($notification);
        
        try {
            // Deletar os readers (pivot)
            \DB::table('notification_user')
                ->where('notification_id', $notif->id)
                ->delete();
            
            // Deletar a notificação
            \DB::table('notifications')
                ->where('id', $notif->id)
                ->delete();
            
            return redirect()->route('admin.notifications.index')
                ->with('success', 'Notificação deletada permanentemente do histórico!');
        } catch (\Exception $e) {
            \Log::error('Erro ao deletar notificação: ' . $e->getMessage());
            return redirect()->route('admin.notifications.index')
                ->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }
    }
}
