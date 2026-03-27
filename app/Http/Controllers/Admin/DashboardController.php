<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\User;
use App\Models\ContentRequest;
use App\Models\MoviePlayLink;
use App\Models\EpisodeLink;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'movies'   => Movie::count(),
            'series'   => Serie::count(),
            'users'    => User::count(),
            'requests' => ContentRequest::where('status', 'pending')->count(),
            'movie_links' => MoviePlayLink::count(),
            'episode_links' => EpisodeLink::count(),
        ];

        // Últimos usuários para o mini-tabela do dash
        $recentUsers = User::latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}
