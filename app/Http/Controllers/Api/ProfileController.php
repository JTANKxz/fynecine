<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Lista todos os perfis do usuário atual.
     */
    public function index(Request $request): JsonResponse
    {
        $profiles = $request->user()->profiles;
        return response()->json($profiles);
    }

    /**
     * Cria um novo perfil (limite de 5).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:50'],
            'avatar'  => ['nullable', 'string', 'max:255'],
            'is_kids' => ['boolean'],
            'pin'     => ['nullable', 'string', 'size:4']
        ]);

        $user = $request->user();
        $maxProfiles = $user->maxProfilesCount();
        $currentCount = $user->profiles()->count();

        if ($currentCount >= $maxProfiles) {
            return response()->json([
                'message' => "Seu plano atual ({$user->plan_type}) permite um máximo de {$maxProfiles} perfis."
            ], 403);
        }

        $profileData = $request->only('name', 'avatar', 'is_kids', 'pin');
        if ($currentCount === 0) {
            $profileData['is_main'] = true;
        }

        $profile = $user->profiles()->create($profileData);

        return response()->json($profile, 201);
    }

    /**
     * Exibe um perfil específico (se pertencer ao usuário).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->profiles()->findOrFail($id);
        return response()->json($profile);
    }

    /**
     * Atualiza um perfil.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->profiles()->findOrFail($id);

        $validated = $request->validate([
            'name'    => ['sometimes', 'string', 'max:50'],
            'avatar'  => ['nullable', 'string', 'max:255'],
            'is_kids' => ['boolean'],
            'pin'     => ['nullable', 'string', 'size:4']
        ]);

        if ($request->has('pin')) {
            $validated['pin'] = $request->pin;
        }

        $profile->update($validated);

        return response()->json($profile);
    }

    /**
     * Deleta um perfil (exigindo que exista pelo menos 1).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->profiles()->count() <= 1) {
            return response()->json([
                'message' => 'Você precisa ter pelo menos 1 perfil na conta.'
            ], 403);
        }

        $profile = $user->profiles()->findOrFail($id);
        $profile->delete();

        return response()->json(['message' => 'Perfil deletado com sucesso.']);
    }

    /**
     * Verifica o PIN do perfil selecionado.
     * POST /api/profiles/{id}/verify-pin
     */
    public function verifyPin(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:4']
        ]);

        $profile = $request->user()->profiles()->findOrFail($id);

        if ($profile->pin !== $request->pin) {
            return response()->json(['message' => 'PIN incorreto. Tente novamente.'], 403);
        }

        return response()->json(['message' => 'Acesso autorizado.', 'profile_id' => $profile->id]);
    }
}
