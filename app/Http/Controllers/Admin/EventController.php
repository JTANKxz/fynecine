<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventLink;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('home_team', 'like', "%{$search}%")
                ->orWhere('away_team', 'like', "%{$search}%");
        }

        $events = $query->orderBy('start_time', 'desc')->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'home_team' => 'nullable|string|max:100',
            'away_team' => 'nullable|string|max:100',
            'home_team_id' => 'nullable|exists:teams,id',
            'away_team_id' => 'nullable|exists:teams,id',
            'image_url' => 'nullable|url',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_global') || $request->has('is_active');
        $data['home_team_id'] = $request->input('home_team_id') ?: null;
        $data['away_team_id'] = $request->input('away_team_id') ?: null;

        Event::create($data);

        return redirect()->route('admin.events.index')->with('success', 'Evento criado com sucesso!');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'home_team' => 'nullable|string|max:100',
            'away_team' => 'nullable|string|max:100',
            'home_team_id' => 'nullable|exists:teams,id',
            'away_team_id' => 'nullable|exists:teams,id',
            'image_url' => 'nullable|url',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['home_team_id'] = $request->input('home_team_id') ?: null;
        $data['away_team_id'] = $request->input('away_team_id') ?: null;

        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', 'Evento atualizado!');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return back()->with('success', 'Evento excluído!');
    }

    // =============================================
    // LINKS DO EVENTO
    // =============================================

    public function links(Event $event)
    {
        $links = $event->links;
        return view('admin.events.links.index', compact('event', 'links'));
    }

    public function createLink(Event $event)
    {
        return view('admin.events.links.create', compact('event'));
    }

    public function storeLink(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'type' => 'required|in:embed,direct,m3u8,mp4,mkv,custom',
            'player_sub' => 'required|in:free,premium',
        ]);

        $event->links()->create($data);

        return redirect()->route('admin.events.links', $event->id)->with('success', 'Link adicionado!');
    }

    public function editLink(EventLink $link)
    {
        $event = $link->event;
        return view('admin.events.links.edit', compact('link', 'event'));
    }

    public function updateLink(Request $request, EventLink $link)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'type' => 'required|in:embed,direct,m3u8,mp4,mkv,custom',
            'player_sub' => 'required|in:free,premium',
        ]);

        $link->update($data);

        return redirect()->route('admin.events.links', $link->event_id)->with('success', 'Link atualizado!');
    }

    public function deleteLink(EventLink $link)
    {
        $link->delete();
        return back()->with('success', 'Link removido!');
    }
}
