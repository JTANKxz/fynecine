<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultHomeSection;
use App\Models\AdultGallery;
use App\Models\AdultModel;
use App\Models\AdultCategory;
use App\Models\AdultCollection;
use App\Models\AdultMedia;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $sections = AdultHomeSection::where('is_active', true)->orderBy('order')->get();
        
        $data = $sections->map(function ($section) {
            $items = collect();
            $itemView = 'default';
            
            switch ($section->type) {
                case 'trending':
                    $items = AdultGallery::where('is_active', true)->orderByDesc('created_at')->limit($section->limit)->get();
                    break;
                case 'recent':
                    $galleries = AdultGallery::where('is_active', true)->orderByDesc('id')->limit($section->limit)->get();
                    $media = AdultMedia::whereNull('adult_gallery_id')->where('is_active', true)->orderByDesc('id')->limit($section->limit)->get();
                    $items = $galleries->concat($media)->sortByDesc('created_at')->take($section->limit)->values();
                    break;
                case 'models_carousel':
                    $items = AdultModel::where('is_active', true)->limit($section->limit)->get();
                    $itemView = 'models';
                    break;
                case 'video_grid':
                    $items = AdultMedia::where('is_active', true)->where('type', 'video')->orderByDesc('id')->limit($section->limit)->get();
                    $itemView = 'video_grid';
                    break;
                case 'photo_grid':
                    $items = AdultMedia::where('is_active', true)->where('type', 'image')->orderByDesc('id')->limit($section->limit)->get();
                    $itemView = 'photo_grid';
                    break;
                case 'galleries_grid':
                    $items = AdultGallery::where('is_active', true)->orderByDesc('id')->limit($section->limit)->get();
                    $itemView = 'galleries_grid';
                    break;
                case 'collections':
                    $items = AdultCollection::where('is_active', true)
                        ->withCount('galleries')
                        ->orderBy('order')
                        ->get();
                    $itemView = 'collections';
                    break;
                case 'categories_grid':
                    $items = AdultCategory::where('is_active', true)->orderBy('order')->get();
                    $itemView = 'categories';
                    break;
                case 'custom':
                    $manualItems = $section->manualItems()->orderBy('order')->get();
                    $items = $manualItems->map(function($pivot) {
                        return $pivot->target;
                    })->filter();
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
