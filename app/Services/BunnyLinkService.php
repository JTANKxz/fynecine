<?php

namespace App\Services;

use App\Models\AppConfig;

class BunnyLinkService
{
    /**
     * Gera uma URL assinada para o Bunny CDN usando SHA256 e token de diretório.
     *
     * @param string $url URL original (ex: https://cdn.net/video/playlist.m3u8)
     * @param string|null $linkPath Caminho customizado para o token (ex: /video/)
     * @param int|null $expirationHours Horas de expiração (default: 4)
     * @return string URL assinada
     */
    public static function generateSignedUrl(string $url, ?string $linkPath = null, ?int $expirationHours = 4)
    {
        $config = AppConfig::getSettings();
        $securityKey = $config->bunny_security_key;
        
        if (!$securityKey) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $fullPath = $parsedUrl['path'] ?? '';

        // Se URL não tem host, usamos o padrão global
        if (empty($host) && $config->bunny_cdn_url) {
            $host = $config->bunny_cdn_url;
            // Se o usuário inseriu apenas o path, garantimos que comece com /
            $fullPath = '/' . ltrim($fullPath, '/');
        }

        if (empty($host)) {
            return $url; // Não tem como gerar link assinado sem host
        }

        // Se não informar o linkPath, extraímos o diretório da URL
        if (!$linkPath) {
            $linkPath = dirname($fullPath);
            if ($linkPath === '/' || $linkPath === '\\') {
                $linkPath = '/';
            } else {
                $linkPath = rtrim($linkPath, '/') . '/';
            }
        }

        // Garante que o linkPath comece e termine com /
        $linkPath = '/' . ltrim($linkPath, '/');
        $linkPath = rtrim($linkPath, '/') . '/';

        // Expiração (em segundos)
        $expires = time() + (($expirationHours ?? 4) * 3600);

        // Assinatura: security_key + path + expires
        // Bunny CDN usa SHA256 HASH -> base64
        $tokenStr = $securityKey . $linkPath . $expires;
        $hash = hash('sha256', $tokenStr, true);
        $token = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($hash));

        // Formato final: https://host/bcdn_token=TOKEN&expires=TIMESTAMP&token_path=PATH/actual_path
        // Nota: O token_path na query string deve ser URL encoded
        $signedUrl = sprintf(
            "%s://%s/bcdn_token=%s&expires=%s&token_path=%s%s",
            $scheme,
            $host,
            $token,
            $expires,
            urlencode($linkPath),
            $fullPath
        );

        return $signedUrl;
    }
}
