<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Lista todos os dispositivos (tokens) ativos do usuário.
     * Somente disponível para o perfil titular (is_main).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeMainProfile($request);

        $tokens = $request->user()->tokens()->orderBy('last_used_at', 'desc')->get()->map(function ($token) use ($request) {
            return [
                'id'          => $token->id,
                'is_current'  => $token->id === $request->user()->currentAccessToken()->id,
                'device_name' => $token->device_name ?: 'Dispositivo Desconhecido',
                'device_type' => $token->device_type ?: 'mobile',
                'location'    => $token->location ?: 'Localização desconhecida',
                'ip_address'  => $token->ip_address,
                'last_active' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Nunca',
                'created_at'  => $token->created_at ? $token->created_at->format('d/m/Y H:i') : null,
                'device_uuid' => $token->device_uuid,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $tokens
        ]);
    }

    /**
     * Revoga um token específico (Desloga o dispositivo).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $this->authorizeMainProfile($request);

        $token = $request->user()->tokens()->where('id', $id)->first();

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Dispositivo não encontrado.'], 404);
        }

        $token->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Dispositivo desconectado com sucesso.'
        ]);
    }

    /**
     * Valida se o perfil logado no app é o titular.
     * Espera o header X-Profile-Id.
     */
    private function authorizeMainProfile(Request $request): void
    {
        $profileId = $request->header('X-Profile-Id');

        if (!$profileId) {
            abort(response()->json([
                'status'  => false,
                'message' => 'ID do perfil não fornecido.'
            ], 403));
        }

        $profile = Profile::where('id', $profileId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$profile || !$profile->is_main) {
            abort(response()->json([
                'status'  => false,
                'message' => 'Esta ação é restrita ao perfil titular da conta.'
            ], 403));
        }
    }
}
