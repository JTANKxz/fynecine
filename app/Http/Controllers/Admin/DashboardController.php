<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\User;
use App\Models\ContentRequest;
use App\Models\MoviePlayLink;
use App\Models\EpisodeLink;
use App\Models\TvChannel;
use App\Models\Network;
use App\Models\Event;
use App\Models\Comment;
use App\Models\ContentCategory;
use App\Models\ContentView;
use App\Models\Team;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'movies'        => Movie::count(),
            'series'        => Serie::count(),
            'channels'      => TvChannel::count(),
            'users'         => User::count(),
            'requests'      => ContentRequest::where('status', 'pending')->count(),
            'movie_links'   => MoviePlayLink::count(),
            'episode_links' => EpisodeLink::count(),
            'networks'      => Network::count(),
            'events'        => Event::count(),
            'comments'      => Comment::count(),
            'teams'         => Team::count(),
            'premium_users' => User::where('plan_type', '!=', 'free')->whereNotNull('plan_type')->count(),
            'views_today'   => ContentView::whereDate('viewed_at', today())->count(),
            'views_week'    => ContentView::where('viewed_at', '>=', now()->subWeek())->count(),
        ];

        // Categorias com contagem
        $categories = ContentCategory::withCount(['movies', 'series'])->orderBy('order')->get();

        // Últimos usuários
        $recentUsers = User::latest()->limit(8)->get();

        // Redes
        $networks = Network::orderBy('name')->get();

        // Conteúdo recente
        $recentMovies = Movie::latest()->limit(5)->get();
        $recentSeries = Serie::latest()->limit(5)->get();

        // Top 5 mais assistidos da semana
        $topMovies = ContentView::select('content_id')
            ->selectRaw('COUNT(*) as views_count')
            ->where('content_type', 'movie')
            ->where('viewed_at', '>=', now()->subWeek())
            ->groupBy('content_id')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $movie = Movie::find($item->content_id);
                if ($movie) $movie->views_count = $item->views_count;
                return $movie;
            })->filter();

        return view('admin.dashboard', compact(
            'stats', 'recentUsers', 'networks', 'categories',
            'recentMovies', 'recentSeries', 'topMovies'
        ));
    }
}
