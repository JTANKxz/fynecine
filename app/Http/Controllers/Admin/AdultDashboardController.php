<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultCategory;
use App\Models\AdultGallery;
use App\Models\AdultMedia;
use App\Models\AdultModel;
use App\Models\AppConfig;

class AdultDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'galleries' => AdultGallery::count(),
            'media' => AdultMedia::count(),
            'models' => AdultModel::count(),
            'categories' => AdultCategory::count(),
            'inactive' => AdultGallery::where('is_active', false)->count()
                + AdultMedia::where('is_active', false)->count()
                + AdultModel::where('is_active', false)->count()
                + AdultCategory::where('is_active', false)->count(),
        ];

        $recentGalleries = AdultGallery::with(['model', 'category'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.adult.dashboard', [
            'config' => AppConfig::getSettings(),
            'stats' => $stats,
            'recentGalleries' => $recentGalleries,
        ]);
    }
}
