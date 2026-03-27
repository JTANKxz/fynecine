<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentView;
use Illuminate\Http\Request;

class ContentViewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id'   => 'required|integer',
            'content_type' => 'required|in:movie,series',
        ]);

        ContentView::create([
            'content_id'   => $validated['content_id'],
            'content_type' => $validated['content_type'],
        ]);

        return response()->json(['success' => true]);
    }
}
