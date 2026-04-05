<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BunnyLinkService;
use App\Models\AppConfig;

// Mock settings
$config = AppConfig::first();
$config->bunny_security_key = 'test_key';
$config->bunny_cdn_url = 'cdn.example.com';
$config->save();

$url = "https://cdn.example.com/videos/movie1/playlist.m3u8";
$signed = BunnyLinkService::generateSignedUrl($url);

echo "Original: $url\n";
echo "Signed: $signed\n";

if (strpos($signed, 'bcdn_token=') !== false && strpos($signed, 'expires=') !== false) {
    echo "SUCCESS: Token and Expires found in URL.\n";
} else {
    echo "FAILURE: Missing token or expires.\n";
}
