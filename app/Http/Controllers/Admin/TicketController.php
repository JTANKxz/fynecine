<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 
use App\Models\Ticket;
use App\Models\Notification;
use App\Models\User;
use App\Models\FcmDevice;
use App\Services\FcmService;
 
class TicketController extends Controller
{
    protected $fcmService;
 
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
 
    public function index(Request $request)
    {
        $query = Ticket::with('user')->latest();
 
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
 
        $tickets = $query->paginate(15)->withQueryString();
 
        return view('admin.tickets.index', compact('tickets'));
    }
 
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,closed,in_progress,answered'
        ]);
 
        $ticket->update($validated);
 
        return back()->with('success', 'Status do ticket atualizado.');
    }
 
    public function respond(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'status' => 'required|in:closed,answered',
            'send_push' => 'boolean',
            'send_in_app' => 'boolean',
            'action_type' => 'nullable|string',
            'action_value' => 'nullable|string',
        ]);
 
        $ticket->update(['status' => $request->status]);
 
        $user = $ticket->user;
        $title = "Resposta ao seu Ticket #{$ticket->id}";
        $content = $request->message;
        
        $isInApp = $request->boolean('send_in_app', true);
        $isPush = $request->boolean('send_push', false);
 
        if ($isInApp) {
            Notification::create([
                'title' => $title,
                'content' => $content,
                'action_type' => $request->action_type ?? 'none',
                'action_value' => $request->action_value,
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
                    'action_type' => $request->action_type ?? 'none',
                    'action_value' => $request->action_value,
                ]);
            }
        }
 
        return back()->with('success', 'Resposta enviada e usuário notificado!');
    }
 
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
 
        return back()->with('success', 'Ticket removido.');
    }
}
