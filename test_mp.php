<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$req = \Illuminate\Http\Request::create('/api/pix/status/5', 'GET');
$response = app()->handle($req);

echo "HTTP STATUS: " . $response->getStatusCode() . "\n";
echo "RESPONSE CONTENT: " . $response->getContent() . "\n";
