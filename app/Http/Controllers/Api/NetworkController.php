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

        return response()->json([
            'id' => $network->id,
            'name' => $network->name,
            'slug' => $network->slug,
            'image_url' => $network->image_url,
            'movies' => $network->movies(),
            'series' => $network->series(),
        ]);
    }
}
