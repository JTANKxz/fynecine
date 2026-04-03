<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Network;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    public function index()
    {
        $networks = Network::orderBy('name')->get();
        return response()->json($networks);
    }

    public function show($idOrSlug, Request $request)
    {
        if (is_numeric($idOrSlug)) {
            $network = Network::findOrFail($idOrSlug);
        } else {
            $network = Network::where('slug', $idOrSlug)->firstOrFail();
        }

        $page = (int) $request->get('page', 1);
        $perPage = 20;

        $movieIds = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_type', 'movie')
            ->pluck('content_id');

        $serieIds = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_type', 'series')
            ->pluck('content_id');

        $movies = \App\Models\Movie::whereIn('id', $movieIds)->latest()->limit(5000)->get()->map(function($m) {
            $m->display_type = 'movie';
            return $m;
        });
        $series = \App\Models\Serie::whereIn('id', $serieIds)->latest()->limit(5000)->get()->map(function($s) {
            $s->display_type = 'series';
            return $s;
        });

        $content = $movies->concat($series)->sortByDesc('created_at')->values();
        $paginated = $content->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'id' => $network->id,
            'name' => $network->name,
            'slug' => $network->slug,
            'image_url' => $network->image_url,
            'data' => $paginated,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $content->count(),
            'last_page' => (int) ceil($content->count() / $perPage)
        ]);
    }
}
