<?php

namespace App\Services;

use App\Models\AppConfig;

class BunnyLinkService
{
    /**
     * Gera uma URL assinada para o Bunny CDN (modo avançado - HLS compatível).
     *
     * @param string $url URL original (ex: https://cdn.net/video/playlist.m3u8)
     * @param string|null $linkPath Caminho do diretório (opcional)
     * @param int|null $expirationHours Horas de expiração
     * @return string URL assinada pronta para ExoPlayer
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
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $fullPath = $parsedUrl['path'] ?? '';

        if (empty($host) || empty($fullPath)) {
            return $url;
        }

        // Garantir formato correto do path
        $fullPath = '/' . ltrim($fullPath, '/');

        // Expiração
        $expires = time() + (($expirationHours ?? 4) * 3600);

        // Se for HLS (.m3u8), usar diretório automaticamente
        if (!$linkPath && str_ends_with($fullPath, '.m3u8')) {
            $linkPath = dirname($fullPath) . '/';
        }

        // Normalizar token_path
        $tokenPath = null;
        if ($linkPath) {
            $tokenPath = '/' . ltrim(rtrim($linkPath, '/'), '/') . '/';
        }

        // 🔐 Montar SIGNATURE (SEM security key aqui)
        $signature = $tokenPath ?: $fullPath;
        $signature .= $expires;

        // Adicionar parâmetros extras (ordem alfabética)
        $extraParams = [];
        if ($tokenPath) {
            $extraParams['token_path'] = $tokenPath;
        }

        ksort($extraParams);

        if (!empty($extraParams)) {
            $paramsRaw = [];
            foreach ($extraParams as $key => $value) {
                $paramsRaw[] = "{$key}={$value}";
            }
            $signature .= implode('&', $paramsRaw);
        }

        // 🔥 HMAC-SHA256 correto
        $hash = hash_hmac('sha256', $signature, $securityKey, true);

        // Base64 URL Safe
        $token = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        // Prefixo obrigatório
        $token = "HS256-" . $token;

        // URL encode do token_path
        $encodedTokenPath = $tokenPath ? urlencode($tokenPath) : '';

        // 🔥 Montar URL FINAL (PATH TOKEN - obrigatório para HLS)
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