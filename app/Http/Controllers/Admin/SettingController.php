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
            'version_code' => ['nullable', 'integer', 'min:0'],
            'update_features' => ['nullable', 'string'],
            
            'autoembed_movie_url' => ['nullable', 'string'],
            'autoembed_serie_url' => ['nullable', 'string'],
        ]);

        $config = AppConfig::getSettings();

        // Checkboxes defaults para false caso não venham no form
        $config->force_login = $request->has('force_login');
        $config->show_onboarding = $request->has('show_onboarding');
        $config->security_mode = $request->has('security_mode');
        $config->update_skippable = $request->has('update_skippable');
        $config->autoembed_movies = $request->has('autoembed_movies');
        $config->autoembed_series = $request->has('autoembed_series');

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
        $config->autoembed_serie_url = $request->autoembed_serie_url;

        $config->save();

        return redirect()->route('admin.settings.edit')->with('success', 'Configurações atualizadas com sucesso.');
    }
}
