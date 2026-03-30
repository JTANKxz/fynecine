<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        // Traz o perfil e a model polimórfica associada (Filme ou Série)
        $comments = Comment::with(['profile.user', 'commentable'])->latest()->paginate(20);
        return view('admin.comments.index', compact('comments'));
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comentário removido.');
    }

    public function toggleApproval(Comment $comment)
    {
        $comment->update(['approved' => !$comment->approved]);
        $status = $comment->approved ? 'aprovado' : 'ocultado';
        return back()->with('success', "Comentário {$status} com sucesso.");
    }
}
