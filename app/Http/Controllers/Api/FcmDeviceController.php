<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmDevice;
use Illuminate\Http\Request;

class FcmDeviceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'device_type'  => 'nullable|string|in:android,ios,web',
            'app_version'  => 'nullable|string',
        ]);

        // Tenta pegar o usuário se houver token Sanctum (opcional nesta rota)
        $user = auth('sanctum')->user();
        
        $device = FcmDevice::updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'user_id'     => $user?->id,
                'device_type' => $request->device_type ?? 'android',
                'app_version' => $request->app_version,
                'last_active' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo registrado com sucesso',
            'device'  => $device
        ]);
    }
}
