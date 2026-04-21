<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Sections: " . \App\Models\AdultHomeSection::count() . "\n";
echo "Active Sections: " . \App\Models\AdultHomeSection::where('is_active', true)->count() . "\n";
echo "Galleries: " . \App\Models\AdultGallery::count() . "\n";
echo "Models: " . \App\Models\AdultModel::count() . "\n";
echo "Categories: " . \App\Models\AdultCategory::count() . "\n";

$sections = \App\Models\AdultHomeSection::where('is_active', true)->orderBy('order')->get();
foreach ($sections as $section) {
    echo "Section ID: {$section->id}, Title: {$section->title}, Type: {$section->type}\n";
    $items = [];
    switch ($section->type) {
        case 'trending': $items = \App\Models\AdultGallery::where('is_active', true)->count(); break;
        case 'models_carousel': $items = \App\Models\AdultModel::where('is_active', true)->count(); break;
        case 'categories_grid': $items = \App\Models\AdultCategory::where('is_active', true)->count(); break;
        default: $items = \App\Models\AdultGallery::where('is_active', true)->count();
    }
    echo " - Items Count: {$items}\n";
}

// Check JSON structure for the first section
$firstSection = \App\Models\AdultHomeSection::where('is_active', true)->first();
$items = [];
switch ($firstSection->type) {
    case 'recent': $items = \App\Models\AdultGallery::where('is_active', true)->limit(5)->get(); break;
    case 'models_carousel': $items = \App\Models\AdultModel::where('is_active', true)->limit(5)->get(); break;
    case 'collections': 
        $items = \App\Models\AdultGallery::where('is_active', true)
            ->whereNotNull('collection')
            ->select('collection', \DB::raw('count(*) as count'), \DB::raw('MAX(cover_url) as cover_url'))
            ->groupBy('collection')
            ->get();
        break;
}

$data = [
    'id' => $firstSection->id,
    'title' => $firstSection->title,
    'type' => $firstSection->type,
    'item_view' => ($firstSection->type == 'collections' ? 'collections' : 'default'),
    'items' => $items
];

echo "\nJSON Structure Sample:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

// Also check a model section
$modelSection = \App\Models\AdultHomeSection::where('type', 'models_carousel')->first();
if ($modelSection) {
    $models = \App\Models\AdultModel::where('is_active', true)->limit(1)->get();
    echo "\nModel Sample:\n";
    echo json_encode($models[0], JSON_PRETTY_PRINT) . "\n";
}
