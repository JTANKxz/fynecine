<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
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
            'is_global' => 'boolean',
            'user_id' => 'required_if:is_global,0|nullable|exists:users,id',
            'image_url' => 'nullable|url',
            'expires_at' => 'nullable|date|after:now',
        ]);

        Notification::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'image_url' => $request->input('image_url'),
            'action_type' => $request->input('action_type'),
            'action_value' => $request->input('action_value'),
            'is_global' => $request->has('is_global'),
            'user_id' => $request->has('is_global') ? null : $request->input('user_id'),
            'expires_at' => $request->input('expires_at'),
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notificação enviada com sucesso!');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'Notificação excluída!');
    }
}
