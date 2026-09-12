<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShortSourceInspector
{
    public function inspect(string $url): array
    {
        $url = trim($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (preg_match('~(?:youtube\.com/(?:shorts/|watch\?v=)|youtu\.be/)([\w-]{6,})~i', $url, $match)) {
            $id = $match[1];
            return $this->embedded('youtube', $id, "https://www.youtube.com/embed/{$id}?enablejsapi=1&playsinline=1", "https://i.ytimg.com/vi/{$id}/hqdefault.jpg");
        }
        if (preg_match('~dailymotion\.com/video/([\w]+)|dai\.ly/([\w]+)~i', $url, $match)) {
            $id = $match[1] ?: $match[2];
            return $this->embedded('dailymotion', $id, "https://www.dailymotion.com/embed/video/{$id}");
        }
        if (preg_match('~tiktok\.com/.*/video/(\d+)~i', $url, $match)) {
            $id = $match[1];
            return $this->embedded('tiktok', $id, "https://www.tiktok.com/player/v1/{$id}?controls=1");
        }
        if (Str::contains($host, 'drive.google.com') && preg_match('~/(?:d/|id=)([\w-]+)~', $url, $match)) {
            return [
                'source_provider' => 'google_drive', 'external_id' => $match[1], 'playback_kind' => 'direct',
                // O player recebe uma URL de download, nunca a página HTML de compartilhamento.
                'source_url' => "https://drive.usercontent.google.com/download?id={$match[1]}&export=download&confirm=t",
                'embed_url' => null, 'thumbnail_url' => null, 'availability' => 'pending',
                'availability_message' => 'O arquivo precisa estar publicamente acessível e aceitar reprodução por faixa.',
                'provider_metadata' => ['requires_public_access' => true, 'original_url' => $url],
            ];
        }

        $result = [
            'source_provider' => 'direct', 'external_id' => null, 'playback_kind' => 'direct',
            'embed_url' => null, 'thumbnail_url' => null, 'availability' => 'pending',
            'availability_message' => 'Aguardando teste do link direto.', 'provider_metadata' => [],
        ];
        if (!$this->isSafePublicUrl($url)) {
            $result['availability'] = 'invalid';
            $result['availability_message'] = 'Use uma URL HTTPS pública; endereços internos não são aceitos.';
            return $result;
        }
        try {
            $response = Http::timeout(7)->withHeaders(['Range' => 'bytes=0-1'])->head($url);
            $type = strtolower((string) $response->header('Content-Type'));
            $isMedia = str_starts_with($type, 'video/') || str_contains($type, 'mpegurl') ||
                str_contains($type, 'dash+xml') || str_contains($type, 'octet-stream') ||
                preg_match('~\.(mp4|m3u8|mpd|webm|mov|mkv)(?:\?|$)~i', $url);
            $result['availability'] = $response->successful() && $isMedia ? 'available' : 'unavailable';
            $result['availability_message'] = $response->successful() && $isMedia
                ? ($type ?: 'Link de mídia respondeu com sucesso.')
                : ($response->successful() ? 'A URL respondeu uma página, não uma mídia direta. Use MP4, HLS ou DASH.' : "Resposta HTTP {$response->status()}.");
            $result['provider_metadata'] = ['content_type' => $type, 'accept_ranges' => $response->header('Accept-Ranges')];
        } catch (\Throwable $e) {
            $result['availability'] = 'unavailable';
            $result['availability_message'] = 'Não foi possível alcançar o link agora.';
        }
        return $result;
    }

    private function embedded(string $provider, string $id, string $embedUrl, ?string $thumbnail = null): array
    {
        return [
            'source_provider' => $provider, 'external_id' => $id, 'playback_kind' => 'embed',
            'embed_url' => $embedUrl, 'thumbnail_url' => $thumbnail, 'availability' => 'available',
            'availability_message' => 'Player oficial incorporado.', 'provider_metadata' => ['official_embed' => true],
        ];
    }

    private function isSafePublicUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') return false;
        $host = (string) parse_url($url, PHP_URL_HOST);
        if (!$host || in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)) return false;
        $ip = gethostbyname($host);
        return $ip === $host || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
