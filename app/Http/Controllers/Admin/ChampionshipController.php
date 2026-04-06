<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use Illuminate\Http\Request;

class ChampionshipController extends Controller
{
    public function index()
    {
        $championships = Championship::orderBy('name')->paginate(15);
        return view('admin.championships.index', compact('championships'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:championships,name'
        ]);

        Championship::create($data);

        return back()->with('success', 'Campeonato criado com sucesso!');
    }

    public function update(Request $request, Championship $championship)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:championships,name,' . $championship->id
        ]);

        $championship->update($data);

        return back()->with('success', 'Campeonato atualizado!');
    }

    public function destroy(Championship $championship)
    {
        $championship->delete();
        return back()->with('success', 'Campeonato excluído!');
    }
}
