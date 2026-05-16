<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use App\Models\AvatarCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function index(Request $request)
    {
        $query = Avatar::with('category');

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('avatar_category_id', $request->category_id);
        }

        $avatars = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $categories = AvatarCategory::orderBy('name')->get();

        return view('admin.avatars.index', compact('avatars', 'categories'));
    }

    public function create()
    {
        $categories = AvatarCategory::orderBy('name')->get();
        return view('admin.avatars.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'avatar_category_id' => 'required|exists:avatar_categories,id',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_url' => 'nullable|url',
            'is_default' => 'nullable|boolean',
            'is_kids' => 'nullable|boolean',
        ]);

        if (!$request->hasFile('image_file') && !$request->image_url) {
            return back()->withErrors(['image_file' => 'Você deve fornecer uma imagem ou uma URL.'])->withInput();
        }

        $imagePath = $request->image_url;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $imagePath = $file->storeAs('avatars', $filename, 'public');
        }

        if ($request->is_default) {
            Avatar::where('is_default', true)->update(['is_default' => false]);
        }
        if ($request->is_kids) {
            Avatar::where('is_kids', true)->update(['is_kids' => false]);
        }

        Avatar::create([
            'avatar_category_id' => $request->avatar_category_id,
            'image' => $imagePath,
            'is_default' => $request->boolean('is_default'),
            'is_kids' => $request->boolean('is_kids'),
        ]);

        return redirect()->route('admin.avatars.index')
            ->with('success', 'Avatar adicionado com sucesso!');
    }

    public function edit(Avatar $avatar)
    {
        $categories = AvatarCategory::orderBy('name')->get();
        return view('admin.avatars.edit', compact('avatar', 'categories'));
    }

    public function update(Request $request, Avatar $avatar)
    {
        $request->validate([
            'avatar_category_id' => 'required|exists:avatar_categories,id',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_url' => 'nullable|url',
            'is_default' => 'nullable|boolean',
            'is_kids' => 'nullable|boolean',
        ]);

        $imagePath = $avatar->image;

        if ($request->hasFile('image_file')) {
            // Delete old file if it exists and is not a URL
            if (!filter_var($avatar->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($avatar->image);
            }
            
            $file = $request->file('image_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $imagePath = $file->storeAs('avatars', $filename, 'public');
        } elseif ($request->image_url) {
             // Delete old file if it exists and is not a URL
             if (!filter_var($avatar->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($avatar->image);
            }
            $imagePath = $request->image_url;
        }

        if ($request->is_default) {
            Avatar::where('id', '!=', $avatar->id)->where('is_default', true)->update(['is_default' => false]);
        }
        if ($request->is_kids) {
            Avatar::where('id', '!=', $avatar->id)->where('is_kids', true)->update(['is_kids' => false]);
        }

        $avatar->update([
            'avatar_category_id' => $request->avatar_category_id,
            'image' => $imagePath,
            'is_default' => $request->boolean('is_default'),
            'is_kids' => $request->boolean('is_kids'),
        ]);

        return redirect()->route('admin.avatars.index')
            ->with('success', 'Avatar atualizado com sucesso!');
    }

    public function destroy(Avatar $avatar)
    {
        if (!filter_var($avatar->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($avatar->image);
        }
        
        $avatar->delete();
        return redirect()->route('admin.avatars.index')
            ->with('success', 'Avatar removido com sucesso!');
    }
}
