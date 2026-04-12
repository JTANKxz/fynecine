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
        // Se NÃO for uma URL do Bunny, retorna o link direto
        if (!self::isBunnyUrl($url)) {
            return $url;
        }

        $config = AppConfig::getSettings();
        
        // Determina se é MP4 (via extensão ou se o link for apenas o nome do arquivo .mp4)
        $isMp4 = str_ends_with(strtolower($url), '.mp4') || str_contains(strtolower($url), '.mp4?');

        if ($isMp4) {
            return self::signMp4Url($url, $expirationHours, $linkPath);
        }
        
        // --- LÓGICA HLS (MANTER ORIGINAL) ---
        $securityKey = env('BUNNY_SECURITY_KEY', $config->bunny_security_key);
        if (!$securityKey) return $url;

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? null;
        $fullPath = $parsedUrl['path'] ?? '';

        if (!$host && !empty($url)) {
            $cdnUrl = $config->bunny_cdn_url;
            $cdnUrlClean = preg_replace('/^https?:\/\//', '', rtrim($cdnUrl, '/'));
            $host = $cdnUrlClean;
            $fullPath = '/' . trim($url, '/') . '/playlist.m3u8';
        }

        if (empty($host) || empty($fullPath)) return $url;

        $fullPath = '/' . ltrim($fullPath, '/');
        $parts = explode('/', trim($fullPath, '/'));
        $videoId = $parts[0] ?? '';
        if (empty($videoId)) return $url;

        $tokenPath = $linkPath ?: ('/' . $videoId . '/');
        $expires = time() + (($expirationHours ?? 4) * 3600);
        $parameterData = "token_path=" . $tokenPath;
        $userIp = '';
        $signature = $securityKey . $tokenPath . $expires . $userIp . $parameterData;
        $hash = hash('sha256', $signature, true);
        $token = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        return "https://{$host}/bcdn_token={$token}&token_path=" . urlencode($tokenPath) . "&expires={$expires}" . $fullPath;
    }

    /**
     * Verifica se a URL pertence ao BunnyCDN configurado.
     */
    private static function isBunnyUrl(string $url): bool
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? null;

        // Se não tem host, assumimos que é um link relativo (nome do arquivo) para o nosso Bunny
        if (!$host) return true;

        $config = AppConfig::getSettings();
        
        // Hosts permitidos (limpando o protocolo se houver)
        $hlsHost = preg_replace('/^https?:\/\//', '', rtrim($config->bunny_cdn_url, '/'));
        $mp4Host = preg_replace('/^https?:\/\//', '', rtrim($config->bunny_mp4_host, '/'));

        $allowedHosts = array_filter([$hlsHost, $mp4Host]);

        foreach ($allowedHosts as $allowed) {
            if (strtolower($host) === strtolower($allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lógica de assinatura para MP4 (Nova)
     */
    private static function signMp4Url(string $url, ?int $expirationHours = 4, ?string $linkPath = null)
    {
        // Se NÃO for uma URL do Bunny, retorna o link direto (redundância de segurança)
        if (!self::isBunnyUrl($url)) {
            return $url;
        }

        $config = AppConfig::getSettings();
        $securityKey = $config->bunny_mp4_key;
        
        // Se não houver chave configurada, não conseguimos assinar, retorna o link original
        if (!$securityKey) return $url;

        $defaultHost = preg_replace('/^https?:\/\//', '', rtrim($config->bunny_mp4_host, '/'));
        
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? $defaultHost;

        if (empty($host)) return $url;
        $path = $parsedUrl['path'] ?? '';

        // Se o path não começar com /, corrige
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Se for só o nome do arquivo no $url
        if (empty($parsedUrl['host']) && !empty($url)) {
            $path = '/' . ltrim($url, '/');
        }

        $expires = time() + (($expirationHours ?? 4) * 3600);
        $userIp = '';
        $parameterData = '';

        // Hashable Base: SecurityKey + path + expires + userIp + parameterData
        $hashPath = $linkPath ?: $path;
        $hashableBase = $securityKey . $hashPath . $expires . $userIp . $parameterData;
        
        $hash = hash('sha256', $hashableBase, true);
        
        // Base64 URL Safe
        $token = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        return "https://{$host}{$path}?token={$token}&expires={$expires}";
    }
}