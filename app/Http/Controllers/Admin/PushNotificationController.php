<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\FcmDevice;
use App\Services\FcmService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request)
    {
        $query = Notification::byPush();

        if ($request->filled('segment')) {
            $query->where('segment', $request->segment);
        }

        if ($request->filled('action')) {
            $query->where('action_type', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('push_status', $request->status);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.push-notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.push-notifications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'action_type' => 'required|in:none,url,movie,series,plans',
            'segment' => 'required|in:all,premium,basic,free,guest,individual',
            'user_id' => 'required_if:segment,individual|nullable|exists:users,id',
            'image_url' => 'nullable|url',
            'big_picture_url' => 'nullable|url',
        ]);

        $isInApp = $request->boolean('is_in_app', false);

        // Só salva no histórico se tiver in-app junto
        // Push puro apenas envia sem registrar
        if ($isInApp) {
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
                'is_in_app' => true,
                'push_status' => 'pending',
            ]);
        } else {
            // Push puro: criar objeto temporário apenas para enviar
            $notification = new Notification([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'image_url' => $request->input('image_url'),
                'big_picture_url' => $request->input('big_picture_url'),
                'action_type' => $request->input('action_type'),
                'action_value' => $request->input('action_value'),
                'segment' => $request->input('segment'),
                'user_id' => $request->input('segment') === 'individual' ? $request->input('user_id') : null,
                'is_in_app' => false,
            ]);
        }

        $this->sendPush($notification);

        $message = $isInApp ? 'Push + In-App enviado com sucesso!' : 'Push enviado com sucesso (sem histórico)!';
        return redirect()->route('admin.push-notifications.index')->with('success', $message);
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

            $this->fcmService->sendPush($tokens, $data);
            
            // Só atualiza status se a notificação foi salva (is_in_app = true)
            if ($notification->id) {
                $notification->update(['push_status' => 'sent']);
            }
        }
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('admin.push-notifications.index')->with('success', 'Histórico de Push removido!');
    }
}
