<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $fillable = [
        'app_name',
        'tmdb_key',
        'api_token_key',
        'custom_message',
        'custom_message_status',
        'force_login',
        'show_onboarding',
        'security_mode',
        'update_type',
        'update_url',
        'update_skippable',
        'update_status',
        'version_code',
        'update_features',
        'autoembed_movies',
        'autoembed_series',
        'autoembed_movie_url',
        'autoembed_serie_url',
    ];

    protected $casts = [
        'force_login' => 'boolean',
        'show_onboarding' => 'boolean',
        'security_mode' => 'boolean',
        'custom_message_status' => 'boolean',
        'update_skippable' => 'boolean',
        'update_status' => 'boolean',
        'autoembed_movies' => 'boolean',
        'autoembed_series' => 'boolean',
    ];

    /**
     * Retorna a configuração global (Sempre a linha ID=1)
     */
    public static function getSettings(): self
    {
        $config = self::first();

        // Se por algum motivo a tabela estiver vazia, cria e retorna a default
        if (!$config) {
            $config = self::create([]);
        }

        return $config;
    }
}
