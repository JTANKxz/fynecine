<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \App\Models\AppConfig::first();
$data = [
    'autoembed_movie_url' => $c->autoembed_movie_url,
    'autoembed_serie_url' => $c->autoembed_serie_url,
    'tmdb' => '{tmdb} in placeholders?',
];

file_put_contents('test_autoembed.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Done";
