<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{
    /**
     * Rota consumida no carregamento do aplicativo móvel/frontend para checar configurações
     * vitais como versão, update_url e force login.
     * GET /api/settings
     */
    public function index(): JsonResponse
    {
        $config = AppConfig::getSettings();

        return response()->json([
            'app_name' => $config->app_name,
            'api_token_key' => $config->api_token_key,
            'custom_message' => $config->custom_message,
            'custom_message_status' => (bool) $config->custom_message_status,
            
            'force_login' => (bool) $config->force_login,
            'show_onboarding' => (bool) $config->show_onboarding,
            
            'update_type' => $config->update_type,
            'update_status' => (bool) $config->update_status,
            'update_url' => $config->update_url,
            'update_skippable' => (bool) $config->update_skippable,
            'version_code' => $config->version_code,
            'update_features' => $config->update_features,
            
            // Retorna o security_mode para que o front também possa omitir itens se quiser
            'security_mode' => (bool) $config->security_mode,
            'is_channels_active' => (bool) $config->is_channels_active,
            'rewards_status' => (bool) $config->rewards_status,
            
            // Redes Sociais
            'instagram_url' => $config->instagram_url,
            'is_instagram_active' => (bool) $config->is_instagram_active,
            'telegram_url' => $config->telegram_url,
            'is_telegram_active' => (bool) $config->is_telegram_active,
            'whatsapp_url' => $config->whatsapp_url,
            'is_whatsapp_active' => (bool) $config->is_whatsapp_active,
            
            // Textos Legais
            'terms_of_use' => $config->terms_of_use,
            'privacy_policy' => $config->privacy_policy,

            // Comentários
            'comments_status' => (bool) $config->comments_status,
            'comments_auto_approve' => (bool) $config->comments_auto_approve,
            'block_vpn' => (bool) $config->block_vpn,
            'block_dns' => (bool) $config->block_dns,

            // Corporate and Multi-Embed
            'app_version' => $config->app_version,
            'contact_email' => $config->contact_email,
            'autoembed_movie_sources' => array_values($config->autoembed_movie_sources ?? []),
            'autoembed_serie_sources' => array_values($config->autoembed_serie_sources ?? []),

            // ========= ADS LOGIC: Disable for Basic/Premium =========
            'ads_banner_status' => (bool) (\Auth::guard('sanctum')->user()?->hasPlan() ? false : $config->ads_banner_status),
            'ads_banner_type' => $config->ads_banner_type,
            'ads_interstitial_status' => (bool) (\Auth::guard('sanctum')->user()?->hasPlan() ? false : $config->ads_interstitial_status),
            'ads_interstitial_type' => $config->ads_interstitial_type,
            'ads_native_status' => (bool) (\Auth::guard('sanctum')->user()?->hasPlan() ? false : $config->ads_native_status),
            'ads_rewarded_status' => (bool) (\Auth::guard('sanctum')->user()?->hasPlan() ? false : $config->ads_rewarded_status),
            // ========================================================
            
            'admob_app_id' => $config->admob_app_id,
            'admob_banner_id' => $config->admob_banner_id,
            'admob_interstitial_id' => $config->admob_interstitial_id,
            'admob_native_id' => $config->admob_native_id,
            'admob_rewarded_id' => $config->admob_rewarded_id,

            'custom_banner_image' => $config->custom_banner_image ? 
                (filter_var($config->custom_banner_image, FILTER_VALIDATE_URL) ? $config->custom_banner_image : asset('storage/' . $config->custom_banner_image)) : null,
            'custom_banner_link' => $config->custom_banner_link,
            
            'custom_interstitial_type' => $config->custom_interstitial_type,
            'custom_interstitial_media' => $config->custom_interstitial_media ? 
                (filter_var($config->custom_interstitial_media, FILTER_VALIDATE_URL) ? $config->custom_interstitial_media : asset('storage/' . $config->custom_interstitial_media)) : null,
            'custom_interstitial_link' => $config->custom_interstitial_link,
            'interstitial_interval' => (int) $config->interstitial_interval,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }
}
