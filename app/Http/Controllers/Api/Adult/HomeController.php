<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultHomeSection;
use App\Models\AdultGallery;
use App\Models\AdultModel;
use App\Models\AdultCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $sections = AdultHomeSection::where('is_active', true)->orderBy('order')->get();
        
        $data = $sections->map(function ($section) {
            $items = [];
            $itemView = 'default'; // can be used by android to determine layout
            
            switch ($section->type) {
                case 'trending':
                    $items = AdultGallery::where('is_active', true)->orderByDesc('created_at')->limit($section->limit)->get();
                    break;
                case 'recent':
                    $items = AdultGallery::where('is_active', true)->orderByDesc('id')->limit($section->limit)->get();
                    break;
                case 'models_carousel':
                    $items = AdultModel::where('is_active', true)->limit($section->limit)->get();
                    $itemView = 'models';
                    break;
                case 'video_grid':
                    $items = AdultGallery::where('is_active', true)->whereIn('type', ['video', 'both'])->orderByDesc('id')->limit($section->limit)->get();
                    break;
                case 'photo_grid':
                    $items = AdultGallery::where('is_active', true)->whereIn('type', ['photo', 'both'])->orderByDesc('id')->limit($section->limit)->get();
                    break;
                case 'collections':
                    // Group by collection names that are not null
                    $items = AdultGallery::where('is_active', true)
                        ->whereNotNull('collection')
                        ->select('collection', \DB::raw('count(*) as count'), \DB::raw('MAX(cover_url) as cover_url'))
                        ->groupBy('collection')
                        ->get();
                    $itemView = 'collections';
                    break;
                case 'categories_grid':
                    $items = AdultCategory::where('is_active', true)->orderBy('order')->get();
                    $itemView = 'categories';
                    break;
                case 'custom':
                    $items = AdultGallery::where('is_active', true)->limit($section->limit)->get();
                    break;
                default:
                    $items = AdultGallery::where('is_active', true)->limit($section->limit)->get();
            }

            return [
                'id' => $section->id,
                'title' => $section->title,
                'type' => $section->type,
                'item_view' => $itemView,
                'items' => $items
            ];
        });

        return response()->json($data);
    }
}
