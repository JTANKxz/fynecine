<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::orderBy('name')->paginate(20);
        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
            'image_upload' => 'nullable|image|max:2048',
        ]);

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('teams', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        Team::create([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ]);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Time cadastrado com sucesso!');
    }

    public function edit(Team $team)
    {
        return view('admin.teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
            'image_upload' => 'nullable|image|max:2048',
        ]);

        $imageUrl = $validated['image_url'] ?? $team->image_url;

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('teams', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $team->update([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ]);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Time atualizado com sucesso!');
    }

    public function destroy(Team $team)
    {
        $team->update(['image_url' => null]);
        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Time removido com sucesso!');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $teams = Team::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'image_url']);

        return response()->json($teams);
    }
}
