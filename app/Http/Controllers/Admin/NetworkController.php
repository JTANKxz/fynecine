<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Network;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NetworkController extends Controller
{
    public function index(Request $request)
    {
        $query = Network::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $networks = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.networks.index', compact('networks'));
    }

    public function create()
    {
        return view('admin.networks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
        ]);

        Network::create([
            'name'      => $validated['name'],
            'slug'      => Str::slug($validated['name']),
            'image_url' => $validated['image_url'] ?? null,
        ]);

        return redirect()->route('admin.networks.index')
            ->with('success', 'Network criada com sucesso!');
    }

    public function edit(Network $network)
    {
        return view('admin.networks.edit', compact('network'));
    }

    public function update(Request $request, Network $network)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
        ]);

        $network->update([
            'name'      => $validated['name'],
            'slug'      => Str::slug($validated['name']),
            'image_url' => $validated['image_url'] ?? null,
        ]);

        return redirect()->route('admin.networks.index')
            ->with('success', 'Network atualizada!');
    }

    public function destroy(Network $network)
    {
        $network->delete();
        return redirect()->route('admin.networks.index')
            ->with('success', 'Network removida!');
    }

    // ========== GERENCIAR CONTEÚDO ==========

    public function content(Network $network, Request $request)
    {
        $movieIds = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_type', 'movie')
            ->pluck('content_id');

        $serieIds = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_type', 'series')
            ->pluck('content_id');

        $movies = Movie::whereIn('id', $movieIds)->get();
        $series = Serie::whereIn('id', $serieIds)->get();

        return view('admin.networks.content', compact('network', 'movies', 'series'));
    }

    public function searchContent(Request $request)
    {
        $search = $request->input('q', '');
        $type = $request->input('type', 'movie');

        if ($type === 'movie') {
            $results = Movie::where('title', 'like', "%{$search}%")
                ->limit(10)->get(['id', 'title as name', 'poster_path']);
        } else {
            $results = Serie::where('name', 'like', "%{$search}%")
                ->limit(10)->get(['id', 'name', 'poster_path']);
        }

        return response()->json($results);
    }

    public function addContent(Request $request, Network $network)
    {
        $validated = $request->validate([
            'content_id'   => 'required|integer',
            'content_type' => 'required|in:movie,series',
        ]);

        $exists = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_id', $validated['content_id'])
            ->where('content_type', $validated['content_type'])
            ->exists();

        if (!$exists) {
            \DB::table('network_content')->insert([
                'network_id'   => $network->id,
                'content_id'   => $validated['content_id'],
                'content_type' => $validated['content_type'],
            ]);
        }

        // Buscar detalhes do conteúdo para retorno AJAX
        if ($validated['content_type'] === 'movie') {
            $item = Movie::find($validated['content_id']);
            $name = $item->title;
        } else {
            $item = Serie::find($validated['content_id']);
            $name = $item->name;
        }

        return response()->json([
            'success' => true,
            'message' => 'Conteúdo vinculado!',
            'item'    => [
                'id'           => $item->id,
                'name'         => $name,
                'poster_path'  => $item->poster_path,
                'content_type' => $validated['content_type']
            ]
        ]);
    }

    public function removeContent(Request $request, Network $network)
    {
        $validated = $request->validate([
            'content_id'   => 'required|integer',
            'content_type' => 'required|in:movie,series',
        ]);

        \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_id', $validated['content_id'])
            ->where('content_type', $validated['content_type'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conteúdo removido da network!'
        ]);
    }
}
