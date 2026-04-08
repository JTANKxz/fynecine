<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppConfig;
use App\Services\BunnyLinkService;

echo "--- CONFIGURANDO DADOS DE TESTE ---\n";
$config = AppConfig::getSettings();
$config->bunny_mp4_key = 'ea28117e-26b7-4b42-89fe-9d832a65362d';
$config->bunny_mp4_host = 'fynecinevods.b-cdn.net';
$config->save();
echo "Configurações salvas (MP4 Host: {$config->bunny_mp4_host})\n\n";

echo "--- TESTE DE ASSINATURA DINÂMICA (MP4) ---\n";
$filename = "1156FHD.mp4";
$signedUrl = BunnyLinkService::generateSignedUrl($filename);

echo "Arquivo: $filename\n";
echo "URL Gerada: $signedUrl\n\n";

if (str_contains($signedUrl, "fynecinevods.b-cdn.net") && str_contains($signedUrl, "?token=")) {
    echo "✅ TESTE PASSOU: Assinatura funcionando com dados do banco.\n";
} else {
    echo "❌ TESTE FALHOU: Assinatura ou domínio incorretos.\n";
}
