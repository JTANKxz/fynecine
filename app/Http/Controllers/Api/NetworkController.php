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

        $order = $request->get('order', 'desc');
        if (!in_array($order, ['asc', 'desc'])) { $order = 'desc'; }
        
        $sort = $request->get('sort', 'rating');
        $year = $request->get('year');

        $movieQuery = \App\Models\Movie::whereIn('id', $movieIds);
        if ($year) {
            $movieQuery->where('release_year', $year);
        }
        $movies = $movieQuery->get()->map(function($m) {
            $m->display_type = 'movie';
            $m->year = $m->release_year;
            return $m;
        });

        $serieQuery = \App\Models\Serie::whereIn('id', $serieIds);
        if ($year) {
            $serieQuery->where('first_air_year', $year);
        }
        $series = $serieQuery->get()->map(function($s) {
            $s->display_type = 'series';
            $s->year = $s->first_air_year;
            $s->title = $s->name; // For title sort consistency
            return $s;
        });

        $content = $movies->concat($series);
        
        switch ($sort) {
            case 'year':
                $content = ($order === 'asc') ? $content->sortBy('year') : $content->sortByDesc('year');
                break;
            case 'title':
                $content = ($order === 'asc') ? $content->sortBy('title') : $content->sortByDesc('title');
                break;
            case 'rating':
            default:
                $content = ($order === 'asc') ? $content->sortBy('rating') : $content->sortByDesc('rating');
                break;
        }

        $content = $content->values();
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
