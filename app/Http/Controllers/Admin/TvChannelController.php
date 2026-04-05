<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvChannel;
use App\Models\TvChannelCategory;
use App\Models\TvChannelLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TvChannelController extends Controller
{
    public function index(Request $request)
    {
        $query = TvChannel::with('categories');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $channels = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.channels.index', compact('channels'));
    }

    public function create()
    {
        $categories = TvChannelCategory::orderBy('name')->get();
        return view('admin.channels.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:tv_channel_categories,id',
        ]);

        $channel = TvChannel::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'image_url' => $validated['image_url'] ?? null,
        ]);

        if (!empty($validated['categories'])) {
            $channel->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.channels.index')
            ->with('success', 'Canal criado com sucesso!');
    }

    public function edit(TvChannel $channel)
    {
        $categories = TvChannelCategory::orderBy('name')->get();
        $channel->load('categories');
        return view('admin.channels.edit', compact('channel', 'categories'));
    }

    public function update(Request $request, TvChannel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:tv_channel_categories,id',
        ]);

        $channel->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $channel->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('admin.channels.index')
            ->with('success', 'Canal atualizado com sucesso!');
    }

    public function destroy(TvChannel $channel)
    {
        $channel->delete();
        return redirect()->route('admin.channels.index')
            ->with('success', 'Canal deletado com sucesso!');
    }

    // ========== LINKS ==========

    public function links(TvChannel $channel)
    {
        $links = $channel->links()->orderBy('order')->get();
        return view('admin.channels.links.index', compact('channel', 'links'));
    }

    public function createLink(TvChannel $channel)
    {
        return view('admin.channels.links.create', compact('channel'));
    }

    public function storeLink(Request $request, TvChannel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'url' => 'required|string',
            'type' => 'required|in:embed,m3u8,custom,private',
            'player_sub' => 'required|in:free,premium',
            'link_path' => 'nullable|string',
            'expiration_hours' => 'nullable|integer',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
        ]);
        
        $validated['tv_channel_id'] = $channel->id;

        TvChannelLink::create($validated);

        return redirect()->route('admin.channels.links', $channel->id)
            ->with('success', 'Link adicionado com sucesso!');
    }

    public function editLink(TvChannelLink $link)
    {
        $link->load('channel');
        return view('admin.channels.links.edit', compact('link'));
    }

    public function updateLink(Request $request, TvChannelLink $link)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'url' => 'required|string',
            'type' => 'required|in:embed,m3u8,custom,private',
            'player_sub' => 'required|in:free,premium',
            'link_path' => 'nullable|string',
            'expiration_hours' => 'nullable|integer',
            'user_agent' => 'nullable|string',
            'referer' => 'nullable|string',
            'origin' => 'nullable|string',
            'cookie' => 'nullable|string',
        ]);

        $link->update($validated);

        return redirect()->route('admin.channels.links', $link->tv_channel_id)
            ->with('success', 'Link atualizado com sucesso!');
    }

    public function deleteLink(TvChannelLink $link)
    {
        $channelId = $link->tv_channel_id;
        $link->delete();
        return redirect()->route('admin.channels.links', $channelId)
            ->with('success', 'Link removido!');
    }
}
