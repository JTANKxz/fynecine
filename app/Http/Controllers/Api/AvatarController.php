<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvatarCategory;
use Illuminate\Http\JsonResponse;

class AvatarController extends Controller
{
    /**
     * Retorna a lista de avatares organizada por categorias.
     *
     * GET /api/avatars
     */
    public function index(): JsonResponse
    {
        $categories = AvatarCategory::with('avatars')->orderBy('name')->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'avatars' => $category->avatars->map(function ($avatar) {
                    return [
                        'id' => $avatar->id,
                        'image' => $avatar->image_url,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }
}
