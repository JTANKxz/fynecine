<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$channels = App\Models\TvChannel::with('categories')->get();
foreach ($channels as $ch) {
    $cats = $ch->categories->pluck('slug')->implode(', ');
    echo "Channel: " . $ch->name . " | Slug: " . $ch->slug . " | Categories: [" . $cats . "]" . PHP_EOL;
}
