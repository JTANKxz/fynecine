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

    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
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
            'expires_at' => 'nullable|date|after:now',
        ]);

        $notification = Notification::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'image_url' => $request->input('image_url'),
            'big_picture_url' => $request->input('big_picture_url'),
            'action_type' => $request->input('action_type'),
            'action_value' => $request->input('action_value'),
            'is_global' => $request->input('segment') !== 'individual',
            'user_id' => $request->input('segment') === 'individual' ? $request->input('user_id') : null,
            'segment' => $request->input('segment'),
            'expires_at' => $request->input('expires_at'),
            'push_status' => 'pending',
        ]);

        // Trigger Push Notification logic
        $this->sendPush($notification);

        return redirect()->route('admin.notifications.index')->with('success', 'Notificação criada e enviada para processamento!');
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

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'Notificação excluída!');
    }
}
