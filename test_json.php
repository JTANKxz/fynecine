<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$categories = App\Models\TvChannelCategory::all();
echo json_encode($categories) . PHP_EOL;
