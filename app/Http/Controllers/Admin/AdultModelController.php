<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdultModelController extends Controller
{
    public function index()
    {
        $models = AdultModel::orderBy('name')->get();
        return view('admin.adult.models.index', compact('models'));
    }

    public function create()
    {
        return view('admin.adult.models.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo_url' => 'nullable|url',
            'cover_url' => 'nullable|url',
        ]);

        AdultModel::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'biography' => $request->biography,
            'photo_url' => $request->photo_url,
            'cover_url' => $request->cover_url,
            'instagram' => $request->instagram,
            'twitter' => $request->twitter,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult-models.index')->with('success', 'Modelo criada com sucesso.');
    }

    public function edit(AdultModel $adult_model)
    {
        return view('admin.adult.models.edit', ['model' => $adult_model]);
    }

    public function update(Request $request, AdultModel $adult_model)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo_url' => 'nullable|url',
            'cover_url' => 'nullable|url',
        ]);

        $adult_model->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'biography' => $request->biography,
            'photo_url' => $request->photo_url,
            'cover_url' => $request->cover_url,
            'instagram' => $request->instagram,
            'twitter' => $request->twitter,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult-models.index')->with('success', 'Modelo atualizada com sucesso.');
    }

    public function destroy(AdultModel $adult_model)
    {
        $adult_model->delete();
        return redirect()->route('admin.adult-models.index')->with('success', 'Modelo removida com sucesso.');
    }
}
