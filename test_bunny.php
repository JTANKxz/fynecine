<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BunnyLinkService;

echo "--- TESTE DE ASSINATURA BUNNY (MP4) ---\n";

$filename = "1156FHD.mp4";
$signedUrl = BunnyLinkService::generateSignedUrl($filename);

echo "Arquivo: $filename\n";
echo "URL Gerada: $signedUrl\n\n";

if (str_contains($signedUrl, "fynecinevods.b-cdn.net") && str_contains($signedUrl, "?token=") && str_contains($signedUrl, "&expires=")) {
    echo "✅ TESTE PASSOU: Domínio fixo e parâmetros de token detectados.\n";
} else {
    echo "❌ TESTE FALHOU: Domínio ou parâmetros incorretos.\n";
}

echo "\n--- TESTE DE ASSINATURA BUNNY (HLS - MANUTENÇÃO) ---\n";
// Para HLS, sem extensão, deve gerar o formato antigo
$videoId = "video123";
$signedHls = BunnyLinkService::generateSignedUrl($videoId);
echo "Video ID: $videoId\n";
echo "URL Gerada: $signedHls\n\n";

if (str_contains($signedHls, "/bcdn_token=") && str_contains($signedHls, "/playlist.m3u8")) {
    echo "✅ TESTE PASSOU: Lógica HLS preservada.\n";
} else {
    echo "❌ TESTE FALHOU: Lógica HLS alterada.\n";
}
