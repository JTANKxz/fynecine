<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$categorySlug = 'filmesseries';
$count = App\Models\TvChannel::whereHas('categories', function($q) use ($categorySlug) {
    $q->where('slug', $categorySlug);
})->count();

echo "Channels count for slug '$categorySlug': $count" . PHP_EOL;

$channels = App\Models\TvChannel::whereHas('categories', function($q) use ($categorySlug) {
    $q->where('slug', $categorySlug);
})->get();

foreach ($channels as $channel) {
    echo "- " . $channel->name . " (Slug: " . $channel->slug . ")" . PHP_EOL;
}
