<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_title' => ['nullable', 'string', 'max:255'],
            'maintenance_description' => ['nullable', 'string'],
            'is_channels_active' => ['nullable', 'boolean'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            
            // New arrays for multiple autoembeds
            'autoembed_movie_sources' => ['nullable', 'array'],
            'autoembed_movie_sources.*.name' => ['required_with:autoembed_movie_sources', 'string', 'max:50'],
            'autoembed_movie_sources.*.url' => ['required_with:autoembed_movie_sources', 'string'],
            'autoembed_movie_sources.*.quality' => ['nullable', 'string', 'max:50'],
            'autoembed_movie_sources.*.type' => ['nullable', 'string', 'max:50'],
            'autoembed_movie_sources.*.player_sub' => ['nullable', 'string', 'max:50'],

            'autoembed_serie_sources' => ['nullable', 'array'],
            'autoembed_serie_sources.*.name' => ['required_with:autoembed_serie_sources', 'string', 'max:50'],
            'autoembed_serie_sources.*.url' => ['required_with:autoembed_serie_sources', 'string'],
            'autoembed_serie_sources.*.quality' => ['nullable', 'string', 'max:50'],
            'autoembed_serie_sources.*.type' => ['nullable', 'string', 'max:50'],
            'autoembed_serie_sources.*.player_sub' => ['nullable', 'string', 'max:50'],
            
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

            // Ads
            'admob_app_id' => ['nullable', 'string'],
            'admob_banner_id' => ['nullable', 'string'],
            'admob_interstitial_id' => ['nullable', 'string'],
            'admob_native_id' => ['nullable', 'string'],
            'admob_rewarded_id' => ['nullable', 'string'],
            'ads_banner_status' => ['nullable', 'boolean'],
            'ads_banner_type' => ['nullable', 'in:admob,custom'],
            'ads_interstitial_status' => ['nullable', 'boolean'],
            'ads_interstitial_type' => ['nullable', 'in:admob,custom'],
            'custom_banner_image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'custom_banner_image_url' => ['nullable', 'string'],
            'custom_banner_link' => ['nullable', 'string'],
            'custom_interstitial_type' => ['nullable', 'in:image,video'],
            'custom_interstitial_media_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,mp4', 'max:10240'],
            'custom_interstitial_media_url' => ['nullable', 'string'],
            'custom_interstitial_link' => ['nullable', 'string'],
            'interstitial_interval' => ['nullable', 'integer', 'min:0'],
            'ads_native_status' => ['nullable', 'boolean'],
            'ads_rewarded_status' => ['nullable', 'boolean'],
            
            'bunny_security_key' => ['nullable', 'string', 'max:255'],
            'bunny_cdn_url' => ['nullable', 'string', 'max:255'],
            'bunny_mp4_key' => ['nullable', 'string', 'max:255'],
            'bunny_mp4_host' => ['nullable', 'string', 'max:255'],
        ]);

        $config = AppConfig::getSettings();

        // Checkboxes defaults para false caso não venham no form
        $config->force_login = $request->has('force_login');
        $config->show_onboarding = $request->has('show_onboarding');
        $config->security_mode = $request->has('security_mode');
        $config->maintenance_mode = $request->has('maintenance_mode');
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
        $config->ads_banner_status = $request->has('ads_banner_status');
        $config->ads_interstitial_status = $request->has('ads_interstitial_status');
        $config->ads_native_status = $request->has('ads_native_status');
        $config->ads_rewarded_status = $request->has('ads_rewarded_status');

        // Inputs text/enums
        $config->app_name = $request->app_name;
        $config->tmdb_key = $request->tmdb_key;
        $config->api_token_key = $request->api_token_key;
        $config->custom_message = $request->custom_message;
        $config->maintenance_title = $request->maintenance_title;
        $config->maintenance_description = $request->maintenance_description;
        
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

        // Corporate and Multiple Sources
        $config->app_version = $request->app_version;
        $config->contact_email = $request->contact_email;
        $config->autoembed_movie_sources = $request->autoembed_movie_sources;
        $config->autoembed_serie_sources = $request->autoembed_serie_sources;

        $config->instagram_url = $request->instagram_url;
        $config->telegram_url = $request->telegram_url;
        $config->whatsapp_url = $request->whatsapp_url;
        
        $config->terms_of_use = $request->terms_of_use;
        $config->privacy_policy = $request->privacy_policy;

        // AdMob IDs
        $config->admob_app_id = $request->admob_app_id;
        $config->admob_banner_id = $request->admob_banner_id;
        $config->admob_interstitial_id = $request->admob_interstitial_id;
        $config->admob_native_id = $request->admob_native_id;
        $config->admob_rewarded_id = $request->admob_rewarded_id;
        
        $config->ads_banner_type = $request->ads_banner_type ?? 'admob';
        $config->ads_interstitial_type = $request->ads_interstitial_type ?? 'admob';
        $config->custom_interstitial_type = $request->custom_interstitial_type ?? 'image';
        $config->interstitial_interval = $request->interstitial_interval ?? 3;

        // Custom Banner Handling
        if ($request->hasFile('custom_banner_image_file')) {
            if ($config->custom_banner_image && !filter_var($config->custom_banner_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($config->custom_banner_image);
            }
            $file = $request->file('custom_banner_image_file');
            $filename = time() . '_banner_' . $file->getClientOriginalName();
            $config->custom_banner_image = $file->storeAs('ads', $filename, 'public');
        } elseif ($request->custom_banner_image_url) {
            $config->custom_banner_image = $request->custom_banner_image_url;
        }
        $config->custom_banner_link = $request->custom_banner_link;

        // Custom Interstitial Handling
        if ($request->hasFile('custom_interstitial_media_file')) {
            if ($config->custom_interstitial_media && !filter_var($config->custom_interstitial_media, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($config->custom_interstitial_media);
            }
            $file = $request->file('custom_interstitial_media_file');
            $filename = time() . '_interstitial_' . $file->getClientOriginalName();
            $config->custom_interstitial_media = $file->storeAs('ads', $filename, 'public');
        } elseif ($request->custom_interstitial_media_url) {
            $config->custom_interstitial_media = $request->custom_interstitial_media_url;
        }
        $config->custom_interstitial_link = $request->custom_interstitial_link;

        $config->bunny_security_key = $request->bunny_security_key;
        $config->bunny_cdn_url = $request->bunny_cdn_url;
        $config->bunny_mp4_key = $request->bunny_mp4_key;
        $config->bunny_mp4_host = $request->bunny_mp4_host;

        $config->save();

        return redirect()->route('admin.settings.edit')->with('success', 'Configurações atualizadas com sucesso.');
    }
}
