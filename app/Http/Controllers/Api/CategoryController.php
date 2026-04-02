<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ContentCategory::where('is_active', true)
            ->where('is_nav_visible', true)
            ->orderBy('order')
            ->get();

        return response()->json($categories);
    }
}
