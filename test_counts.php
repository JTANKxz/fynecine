<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\TvChannelCategory::all() as $cat) {
    echo $cat->id . ' | ' . $cat->name . ' | ' . $cat->slug . ' | Count: ' . $cat->channels()->count() . PHP_EOL;
}
