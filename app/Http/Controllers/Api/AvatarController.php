<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
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
        $categories = AvatarCategory::with('avatars')->orderBy('display_order', 'asc')->orderBy('name', 'asc')->get();
        $defaultAvatarId = AppConfig::getSettings()->default_avatar_p1;

        $data = $categories->map(function ($category) use ($defaultAvatarId) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'avatars' => $category->avatars->map(function ($avatar) use ($defaultAvatarId) {
                    return [
                        'id' => $avatar->id,
                        'image' => $avatar->image_url,
                        'is_default' => (int) $avatar->id === (int) $defaultAvatarId,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }
}
