<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultModel;
use Illuminate\Http\Request;

class AdultModelController extends Controller
{
    public function index()
    {
        $models = AdultModel::where('is_active', true)->orderBy('name')->paginate(20);
        return response()->json($models);
    }

    public function show($idOrSlug)
    {
        $model = AdultModel::where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->with(['galleries' => function($q) {
                $q->where('is_active', true)->orderBy('order');
            }])
            ->firstOrFail();

        return response()->json($model);
    }
}
