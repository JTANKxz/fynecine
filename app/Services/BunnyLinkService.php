<?php

namespace App\Services;

use App\Models\AppConfig;

class BunnyLinkService
{
    public static function generateSignedUrl(
        string $url,
        ?string $linkPath = null,
        ?int $expirationHours = 4
    ) {
        $config = AppConfig::getSettings();
        $securityKey = env('BUNNY_SECURITY_KEY', $config->bunny_security_key);

        if (!$securityKey) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? null;
        $fullPath = $parsedUrl['path'] ?? '';

        // 🔹 Se vier só o ID do vídeo
        if (!$host && !empty($url)) {
            $cdnUrl = $config->bunny_cdn_url;
            $cdnUrlClean = preg_replace('/^https?:\/\//', '', rtrim($cdnUrl, '/'));

            $host = $cdnUrlClean;
            $fullPath = '/' . trim($url, '/') . '/playlist.m3u8';
        }

        if (empty($host) || empty($fullPath)) {
            return $url;
        }

        // 🔹 Normaliza path
        $fullPath = '/' . ltrim($fullPath, '/');

        /**
         * 🔥 EXTRAÇÃO SEGURA DO VIDEO ID
         * Evita erro do dirname()
         */
        $parts = explode('/', trim($fullPath, '/'));
        $videoId = $parts[0] ?? '';

        if (empty($videoId)) {
            return $url;
        }

        // 🔥 token_path EXACT igual ao Node
        $tokenPath = '/' . $videoId . '/';

        // 🔹 Expiração
        $expires = time() + (($expirationHours ?? 4) * 3600);

        /**
         * 🔥 ASSINATURA (ORDEM CRÍTICA)
         * SecurityKey + tokenPath + expires + userIp + parameterData
         */
        $parameterData = "token_path=" . $tokenPath;
        $userIp = '';

        $signature = $securityKey . $tokenPath . $expires . $userIp . $parameterData;

        // 🔹 Hash SHA256 binário
        $hash = hash('sha256', $signature, true);

        // 🔹 Base64 URL Safe (igual Node)
        $token = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        /**
         * 🔥 URL FINAL (SEM ? e SEM ERRO DE FORMATO)
         */
        $signedUrl = "https://{$host}/bcdn_token={$token}"
            . "&token_path=" . urlencode($tokenPath)
            . "&expires={$expires}"
            . $fullPath;

        return $signedUrl;
    }
}