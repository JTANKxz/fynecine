<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $config = AppConfig::getSettings();
        return view('admin.settings.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'tmdb_key' => ['nullable', 'string'],
            'api_token_key' => ['nullable', 'string'],
            'custom_message' => ['nullable', 'string'],
            
            'update_type' => ['required', 'in:none,in_app,external'],
            'update_url' => ['nullable', 'string'],
            'update_status' => ['nullable', 'boolean'],
            'version_code' => ['nullable', 'integer', 'min:0'],
            'update_features' => ['nullable', 'string'],
            'custom_message_status' => ['nullable', 'boolean'],
            'security_mode' => ['nullable', 'boolean'],
            'is_channels_active' => ['nullable', 'boolean'],
            
            'autoembed_movie_url' => ['nullable', 'string'],
            'autoembed_movie_name' => ['nullable', 'string', 'max:50'],
            'autoembed_movie_quality' => ['nullable', 'string', 'max:50'],
            'autoembed_movie_type' => ['nullable', 'string', 'max:50'],
            'autoembed_movie_player_sub' => ['nullable', 'string', 'max:50'],
            'autoembed_serie_url' => ['nullable', 'string'],
            'autoembed_serie_name' => ['nullable', 'string', 'max:50'],
            'autoembed_serie_quality' => ['nullable', 'string', 'max:50'],
            'autoembed_serie_type' => ['nullable', 'string', 'max:50'],
            'autoembed_serie_player_sub' => ['nullable', 'string', 'max:50'],

            'instagram_url' => ['nullable', 'url'],
            'is_instagram_active' => ['nullable', 'boolean'],
            
            'telegram_url' => ['nullable', 'url'],
            'is_telegram_active' => ['nullable', 'boolean'],
            
            'whatsapp_url' => ['nullable', 'url'],
            'is_whatsapp_active' => ['nullable', 'boolean'],
            
            'terms_of_use' => ['nullable', 'string'],
            'privacy_policy' => ['nullable', 'string'],
            
            'comments_status' => ['nullable', 'boolean'],
            'comments_auto_approve' => ['nullable', 'boolean'],
            'block_vpn' => ['nullable', 'boolean'],
            'block_dns' => ['nullable', 'boolean'],
        ]);

        $config = AppConfig::getSettings();

        // Checkboxes defaults para false caso não venham no form
        $config->force_login = $request->has('force_login');
        $config->show_onboarding = $request->has('show_onboarding');
        $config->security_mode = $request->has('security_mode');
        $config->is_channels_active = $request->has('is_channels_active');
        $config->custom_message_status = $request->has('custom_message_status');
        $config->update_skippable = $request->has('update_skippable');
        $config->update_status = $request->has('update_status');
        $config->autoembed_movies = $request->has('autoembed_movies');
        $config->autoembed_series = $request->has('autoembed_series');
        $config->is_instagram_active = $request->has('is_instagram_active');
        $config->is_telegram_active = $request->has('is_telegram_active');
        $config->is_whatsapp_active = $request->has('is_whatsapp_active');
        $config->comments_status = $request->has('comments_status');
        $config->comments_auto_approve = $request->has('comments_auto_approve');
        $config->block_vpn = $request->has('block_vpn');
        $config->block_dns = $request->has('block_dns');

        // Inputs text/enums
        $config->app_name = $request->app_name;
        $config->tmdb_key = $request->tmdb_key;
        $config->api_token_key = $request->api_token_key;
        $config->custom_message = $request->custom_message;
        
        $config->update_type = $request->update_type;
        $config->update_url = $request->update_url;
        $config->version_code = $request->version_code;
        $config->update_features = $request->update_features;
        
        $config->autoembed_movie_url = $request->autoembed_movie_url;
        $config->autoembed_movie_name = $request->autoembed_movie_name ?? 'Auto Player';
        $config->autoembed_movie_quality = $request->autoembed_movie_quality ?? 'HD';
        $config->autoembed_movie_type = $request->autoembed_movie_type ?? 'embed';
        $config->autoembed_movie_player_sub = $request->autoembed_movie_player_sub ?? 'free';

        $config->autoembed_serie_url = $request->autoembed_serie_url;
        $config->autoembed_serie_name = $request->autoembed_serie_name ?? 'Auto Player';
        $config->autoembed_serie_quality = $request->autoembed_serie_quality ?? 'HD';
        $config->autoembed_serie_type = $request->autoembed_serie_type ?? 'embed';
        $config->autoembed_serie_player_sub = $request->autoembed_serie_player_sub ?? 'free';

        $config->instagram_url = $request->instagram_url;
        $config->telegram_url = $request->telegram_url;
        $config->whatsapp_url = $request->whatsapp_url;
        
        $config->terms_of_use = $request->terms_of_use;
        $config->privacy_policy = $request->privacy_policy;

        $config->save();

        return redirect()->route('admin.settings.edit')->with('success', 'Configurações atualizadas com sucesso.');
    }
}
