<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class InAppNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::inApp();

        if ($request->filled('segment')) {
            $query->where('segment', $request->segment);
        }

        if ($request->filled('action')) {
            $query->where('action_type', $request->action);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.in-app-notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.in-app-notifications.create');
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

        Notification::create([
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
            'is_in_app' => true,
            'push_status' => 'none',
        ]);

        return redirect()->route('admin.in-app-notifications.index')->with('success', 'Notificação In-App criada com sucesso!');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('admin.in-app-notifications.index')->with('success', 'Notificação excluída!');
    }
}
