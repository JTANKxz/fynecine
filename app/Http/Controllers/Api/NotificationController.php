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
        $user = auth('sanctum')->user();

        $notifications = Notification::active()
            ->where('is_in_app', true)
            ->where(function ($q) use ($user) {
                if ($user) {
                    // Logged in user segments
                    $segments = ['all'];
                    if ($user->isPremium()) {
                        $segments[] = 'premium';
                    } elseif ($user->isBasic()) {
                        $segments[] = 'basic';
                    } else {
                        $segments[] = 'free';
                    }

                    $q->whereIn('segment', $segments)
                      ->orWhere('user_id', $user->id);
                } else {
                    // Guest user segments
                    $q->whereIn('segment', ['all', 'guest']);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Mapear para incluir status de 'read' baseado na pivot
        $readIds = $user ? $user->readNotifications()->pluck('notification_id')->toArray() : [];

        $data = $notifications->map(function ($n) use ($readIds) {
            return [
                'id'           => $n->id,
                'title'        => $n->title,
                'content'      => $n->content,
                'image_url'    => $n->image_url,
                'action_type'  => $n->action_type,
                'action_value' => $n->action_value,
                'created_at'   => $n->created_at ? $n->created_at->toIso8601String() : null,
                'is_read'      => in_array($n->id, $readIds),
            ];
        });

        // unread_count consistent with filtering
        $unreadCount = $user ? $user->unreadNotificationsCount() : $notifications->count();

        return response()->json([
            'unread_count'  => $unreadCount,
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
