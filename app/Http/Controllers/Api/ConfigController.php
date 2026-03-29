<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;

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
        ]);
    }
}
