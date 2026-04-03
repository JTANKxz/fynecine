<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNoteController extends Controller
{
    public function index()
    {
        $notes = AdminNote::with('user')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.notes.index', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:note,tasks',
            'content' => 'required_if:type,note|nullable|string',
            'tasks' => 'required_if:type,tasks|nullable|array',
            'title' => 'nullable|string|max:255',
            'color' => 'nullable|string|in:purple,emerald,amber,rose,blue,neutral'
        ]);

        $note = AdminNote::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content ?? '',
            'tasks' => $request->tasks,
            'color' => $request->color ?? 'purple',
            'is_pinned' => $request->has('is_pinned')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota criada com sucesso!',
            'note' => $note->load('user'),
            'html' => view('admin.notes.partials.note_card', compact('note'))->render()
        ]);
    }

    public function update(Request $request, AdminNote $note)
    {
        $request->validate([
            'type' => 'required|string|in:note,tasks',
            'content' => 'required_if:type,note|nullable|string',
            'tasks' => 'required_if:type,tasks|nullable|array',
            'title' => 'nullable|string|max:255',
            'color' => 'nullable|string|in:purple,emerald,amber,rose,blue,neutral'
        ]);

        $note->update($request->only(['title', 'content', 'tasks', 'color', 'type']));

        return response()->json([
            'success' => true,
            'message' => 'Nota atualizada com sucesso!',
            'note' => $note->load('user')
        ]);
    }

    public function destroy(AdminNote $note)
    {
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota excluída com sucesso!'
        ]);
    }

    public function togglePin(AdminNote $note)
    {
        $note->is_pinned = !$note->is_pinned;
        $note->save();

        return response()->json([
            'success' => true,
            'message' => $note->is_pinned ? 'Nota fixada!' : 'Nota desafixada!',
            'is_pinned' => $note->is_pinned
        ]);
    }

    public function toggleTask(AdminNote $note, Request $request)
    {
        $index = $request->index;
        $tasks = $note->tasks;

        if (isset($tasks[$index])) {
            $tasks[$index]['done'] = !$tasks[$index]['done'];
            $note->tasks = $tasks;
            $note->save();
        }

        return response()->json([
            'success' => true,
            'tasks' => $note->tasks
        ]);
    }
}
