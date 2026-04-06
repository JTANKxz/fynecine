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
        $expires = time() + (($expirationHours ?? 24) * 3600);

        // linkPath para validação de diretório (token_path)
        if (!$linkPath) {
             // Se for .m3u8, tentamos assinar a pasta pai por padrão
             if (str_ends_with($fullPath, '.m3u8')) {
                 $linkPath = dirname($fullPath) . '/';
             }
        }

        // Parâmetros extras para o Hash (devem estar em ordem alfabética)
        $extraParams = [];
        if ($linkPath) {
            $extraParams['token_path'] = '/' . ltrim(rtrim($linkPath, '/'), '/') . '/';
        }
        ksort($extraParams);

        // Montando a base do Hash SHA256 (Novo sistema Bunny)
        // SHA256_RAW(SecurityKey + Path + Expiration + [IP] + ExtraParams)
        $hashBase = $securityKey . $fullPath . $expires;
        
        // Adiciona parâmetros extras na base do hash se existirem (SEM URL ENCODE na base do hash!)
        if (!empty($extraParams)) {
            $paramsRaw = [];
            foreach ($extraParams as $key => $value) {
                $paramsRaw[] = "{$key}={$value}";
            }
            $hashBase .= implode('&', $paramsRaw);
        }

        // Gerando o Token SHA256 (RAW BINARY)
        $hash = hash('sha256', $hashBase, true);
        $token = base64_encode($hash);
        
        // Normalização Bunny: + por -, / por _ e remove =
        $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

        // Montando a Query String Final
        $query = "token={$token}&expires={$expires}";
        
        foreach ($extraParams as $key => $value) {
            $query .= "&{$key}=" . urlencode($value);
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
