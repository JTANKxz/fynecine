<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Listar notificações do usuário (Globais + Individuais)
     */
    public function index()
    {
        $user = Auth::user();

        $notifications = Notification::active()
            ->where(function ($q) use ($user) {
                $q->where('is_global', true);
                if ($user) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Mapear para incluir status de 'read' baseado na pivot
        $readIds = $user ? $user->readNotifications()->pluck('notification_id')->toArray() : [];

        $data = $notifications->map(function ($n) use ($readIds) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'content' => $n->content,
                'image_url' => $n->image_url,
                'action_type' => $n->action_type,
                'action_value' => $n->action_value,
                'created_at' => $n->created_at,
                'is_read' => in_array($n->id, $readIds),
            ];
        });

        return response()->json([
            'unread_count' => $user ? $user->unreadNotificationsCount() : $notifications->count(),
            'notifications' => $data
        ]);
    }

    /**
     * Marcar uma notificação específica como lida
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $user->readNotifications()->syncWithoutDetaching([
            $id => ['read_at' => now()]
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Marcar TODAS as notificações atuais como lidas
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $unreadIds = $user->unreadNotifications()->pluck('id')->toArray();

        if (!empty($unreadIds)) {
            $syncData = [];
            foreach ($unreadIds as $id) {
                $syncData[$id] = ['read_at' => now()];
            }
            $user->readNotifications()->syncWithoutDetaching($syncData);
        }

        return response()->json([
            'success' => true,
            'marked_count' => count($unreadIds)
        ]);
    }
}
