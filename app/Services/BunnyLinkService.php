<?php

namespace App\Services;

use App\Models\AppConfig;

class BunnyLinkService
{
    /**
     * Gera URL assinada Bunny CDN (compatível com HLS / ExoPlayer / navegador)
     */
    public static function generateSignedUrl(string $url, ?string $linkPath = null, ?int $expirationHours = 4)
    {
        $config = AppConfig::getSettings();

        $securityKey = env('BUNNY_SECURITY_KEY', $config->bunny_security_key);

        if (!$securityKey) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? null;
        $fullPath = $parsedUrl['path'] ?? '';

        // Se for só ID, monta URL completa
        if (!$host && !empty($url)) {
            $cdnUrl = $config->bunny_cdn_url;
            $cdnUrlClean = rtrim(str_replace(['https://', 'http://'], '', $cdnUrl), '/');

            $url = "https://{$cdnUrlClean}/" . ltrim($url, '/') . "/playlist.m3u8";

            $parsedUrl = parse_url($url);
            $host = $parsedUrl['host'] ?? $cdnUrlClean;
            $fullPath = $parsedUrl['path'] ?? '';
        }

        if (empty($host) || empty($fullPath)) {
            return $url;
        }

        $scheme = $parsedUrl['scheme'] ?? 'https';

        // Normaliza path
        $fullPath = '/' . ltrim($fullPath, '/');

        // Expiração
        $expires = time() + (($expirationHours ?? 4) * 3600);

        // Se for HLS, usa diretório
        if (!$linkPath && str_ends_with($fullPath, '.m3u8')) {
            $linkPath = dirname($fullPath) . '/';
        }

        // Normaliza token_path
        $tokenPath = null;
        if ($linkPath) {
            $tokenPath = '/' . trim($linkPath, '/') . '/';
        }

        // 🔥 ASSINATURA CORRETA (IGUAL AO BUNNY)
        $signature = ($tokenPath ?: $fullPath) . $expires;

        // 🔐 HMAC SHA256
        $hash = hash_hmac('sha256', $signature, $securityKey, true);

        // 🔥 TOKEN FINAL (SEM PREFIXO!!)
        $token = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        // URL encode apenas no parâmetro
        $encodedTokenPath = $tokenPath ? urlencode($tokenPath) : '';

        // 🔥 URL FINAL (formato HLS path-based)
        $signedUrl = sprintf(
            "%s://%s/bcdn_token=%s&expires=%s%s%s",
            $scheme,
            $host,
            $token,
            $expires,
            $encodedTokenPath ? "&token_path={$encodedTokenPath}" : '',
            $fullPath
        );

        return $signedUrl;
    }
}