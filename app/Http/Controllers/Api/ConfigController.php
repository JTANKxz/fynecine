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
            
            'force_login' => $config->force_login,
            'show_onboarding' => $config->show_onboarding,
            
            'update_type' => $config->update_type,
            'update_url' => $config->update_url,
            'update_skippable' => $config->update_skippable,
            'version_code' => $config->version_code,
            'update_features' => $config->update_features,
            
            // Retorna o security_mode para que o front também possa omitir itens se quiser
            'security_mode' => $config->security_mode,
        ]);
    }
}
