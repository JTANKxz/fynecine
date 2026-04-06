<?php

namespace App\Services;

use App\Models\AppConfig;

class BunnyLinkService
{
    /**
     * Gera uma URL assinada para o Bunny CDN.
     * Suporta o formato padrão de Query String (?token=...) 
     * compatível com a maioria das Pull Zones.
     *
     * @param string $url URL original (ex: https://cdn.net/video/playlist.m3u8)
     * @param string|null $linkPath Caminho customizado para o token (default: extraído da URL)
     * @param int|null $expirationHours Horas de expiração (default: 4)
     * @return string URL assinada
     */
    public static function generateSignedUrl(string $url, ?string $linkPath = null, ?int $expirationHours = 4)
    {
        $config = AppConfig::getSettings();
        $securityKey = env('BUNNY_SECURITY_KEY', $config->bunny_security_key);
        $cdnUrl = env('BUNNY_CDN_URL', $config->bunny_cdn_url);

        if (!$securityKey) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? $cdnUrl;
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $fullPath = $parsedUrl['path'] ?? '';

        if (empty($host) || empty($fullPath)) {
            return $url;
        }

        // Garante que o fullPath comece com /
        $fullPath = '/' . ltrim($fullPath, '/');

        // Expiração (em segundos)
        $expires = time() + (($expirationHours ?? 4) * 3600);

        // Algoritmo Padrão Bunny (MD5) ou HS256 (dependente da chave)
        // A maioria das Pull Zones usa Security Key + Path + Expires
        // linkPath para validação de diretório (token_path)
        if (!$linkPath) {
             // Por padrão Bunny valida o arquivo específico. 
             // Se quiser validar a pasta toda, informa linkPath.
             $tokenContent = $securityKey . $fullPath . $expires;
        } else {
             $linkPath = '/' . ltrim(rtrim($linkPath, '/'), '/') . '/';
             $tokenContent = $securityKey . $linkPath . $expires;
        }

        // Gerando o Token (MD5 é o padrão universal Bunny para Pull Zones)
        $hash = md5($tokenContent, true);
        $token = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($hash));

        // Montando a Query String
        $query = "token={$token}&expires={$expires}";
        
        if ($linkPath) {
            $query .= "&token_path=" . urlencode($linkPath);
        }

        $signedUrl = sprintf(
            "%s://%s%s?%s",
            $scheme,
            $host,
            $fullPath,
            $query
        );

        return $signedUrl;
    }
}
