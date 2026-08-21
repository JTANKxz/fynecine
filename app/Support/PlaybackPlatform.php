<?php

namespace App\Support;

use Illuminate\Http\Request;

final class PlaybackPlatform
{
    public const ANDROID = 'android';
    public const WEB = 'web';
    public const BOTH = 'both';

    public static function fromRequest(Request $request): string
    {
        $platform = strtolower((string) $request->header('X-Client-Platform', self::ANDROID));

        return in_array($platform, [self::ANDROID, self::WEB], true)
            ? $platform
            : self::ANDROID;
    }

    public static function matches(?string $target, string $platform): bool
    {
        return in_array($target ?: self::BOTH, [self::BOTH, $platform], true);
    }

    public static function sourceHeaders(array $source, string $platform): array
    {
        if ($platform !== self::ANDROID) {
            return [];
        }

        return [
            'user_agent' => $source['user_agent'] ?? null,
            'referer' => $source['referer'] ?? null,
            'origin' => $source['origin'] ?? null,
            'cookie' => $source['cookie'] ?? null,
        ];
    }
}
