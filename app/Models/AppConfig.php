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
        'maintenance_mode',
        'maintenance_title',
        'maintenance_description',
        'is_channels_active',
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
        
        'instagram_url',
        'is_instagram_active',
        'telegram_url',
        'is_telegram_active',
        'whatsapp_url',
        'is_whatsapp_active',
        
        'terms_of_use',
        'privacy_policy',
        
        'comments_status',
        'comments_auto_approve',
        'block_vpn',
        'block_dns',

        'app_version',
        'contact_email',
        'autoembed_movie_sources',
        'autoembed_serie_sources',

        // Ads
        'admob_app_id',
        'admob_banner_id',
        'admob_interstitial_id',
        'admob_native_id',
        'admob_rewarded_id',
        'ads_banner_status',
        'ads_banner_type',
        'ads_interstitial_status',
        'ads_interstitial_type',
        'custom_banner_image',
        'custom_banner_link',
        'custom_interstitial_type',
        'custom_interstitial_media',
        'custom_interstitial_link',
        'interstitial_interval',
        'ads_native_status',
        'ads_rewarded_status',
        'bunny_security_key',
        'bunny_cdn_url',
        'bunny_mp4_key',
        'bunny_mp4_host',
    ];

    protected $casts = [
        'force_login' => 'boolean',
        'show_onboarding' => 'boolean',
        'security_mode' => 'boolean',
        'maintenance_mode' => 'boolean',
        'is_channels_active' => 'boolean',
        'custom_message_status' => 'boolean',
        'update_skippable' => 'boolean',
        'update_status' => 'boolean',
        'autoembed_movies' => 'boolean',
        'autoembed_series' => 'boolean',
        'is_instagram_active' => 'boolean',
        'is_telegram_active' => 'boolean',
        'is_whatsapp_active' => 'boolean',
        'comments_status' => 'boolean',
        'comments_auto_approve' => 'boolean',
        'block_vpn' => 'boolean',
        'block_dns' => 'boolean',
        'autoembed_movie_sources' => 'array',
        'autoembed_serie_sources' => 'array',
        'ads_banner_status' => 'boolean',
        'ads_interstitial_status' => 'boolean',
        'ads_native_status' => 'boolean',
        'ads_rewarded_status' => 'boolean',
        'interstitial_interval' => 'integer',
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
