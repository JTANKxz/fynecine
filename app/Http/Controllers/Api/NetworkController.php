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

    public function show($idOrSlug)
    {
        if (is_numeric($idOrSlug)) {
            $network = Network::findOrFail($idOrSlug);
        } else {
            $network = Network::where('slug', $idOrSlug)->firstOrFail();
        }

        $movieIds = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_type', 'movie')
            ->pluck('content_id');

        $serieIds = \DB::table('network_content')
            ->where('network_id', $network->id)
            ->where('content_type', 'series')
            ->pluck('content_id');

        return response()->json([
            'id' => $network->id,
            'name' => $network->name,
            'slug' => $network->slug,
            'image_url' => $network->image_url,
            'movies' => \App\Models\Movie::whereIn('id', $movieIds)->latest()->limit(50)->get(),
            'series' => \App\Models\Serie::whereIn('id', $serieIds)->latest()->limit(50)->get(),
        ]);
    }
}
