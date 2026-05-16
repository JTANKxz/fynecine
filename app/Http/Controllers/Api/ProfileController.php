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
        $user = $request->user();
        $maxProfiles = $user->maxProfilesCount();

        $profiles = $user->profiles()
            ->orderByDesc('is_main')
            ->orderBy('id', 'asc')
            ->get();

        $profiles->each(function ($profile, $index) use ($maxProfiles) {
            $profile->setAttribute('is_locked', $index >= $maxProfiles);
        });

        return response()->json($profiles);
    }

    /**
     * Verifica se um perfil está bloqueado devido ao limite do plano atual.
     */
    private function isProfileLocked($user, int $profileId): bool
    {
        $maxProfiles = $user->maxProfilesCount();
        
        $profileIds = $user->profiles()
            ->orderByDesc('is_main')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();
            
        $index = array_search($profileId, $profileIds);
        
        if ($index === false) {
            return false;
        }

        return $index >= $maxProfiles;
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
            'pin'     => ['nullable', 'string', 'size:4'],
            'is_adult_enabled' => ['boolean'],
            'adult_pin' => ['nullable', 'string', 'size:4']
        ]);

        $user = $request->user();
        $maxProfiles = $user->maxProfilesCount();
        $currentCount = $user->profiles()->count();

        if ($currentCount >= $maxProfiles) {
            return response()->json([
                'message' => "Seu plano atual ({$user->plan_type}) permite um máximo de {$maxProfiles} perfis."
            ], 403);
        }

        $isKids = $request->boolean('is_kids');
        $profileData = $request->only('avatar', 'is_kids', 'pin', 'is_adult_enabled', 'adult_pin');
        $config = \App\Models\AppConfig::getSettings();
        
        if ($isKids) {
            $profileData['name'] = 'Kids';
            if (empty($profileData['avatar'])) {
                $kidsAvatar = \App\Models\Avatar::find($config->default_avatar_kids);
                if ($kidsAvatar) {
                    $profileData['avatar'] = $kidsAvatar->image;
                } else {
                    $kidsAvatarFallback = \App\Models\Avatar::where('is_kids', true)->first();
                    if ($kidsAvatarFallback) {
                        $profileData['avatar'] = $kidsAvatarFallback->image;
                    }
                }
            }
        } else {
            // Se o app enviar nome, usa ele. Senão, gera Perfil X
            if ($request->has('name') && !empty($request->name)) {
                $profileData['name'] = $request->name;
            } else {
                $profileData['name'] = 'Perfil ' . ($currentCount + 1);
            }
            
            // Avatar padrão se não enviado
            if (empty($profileData['avatar'])) {
                $slot = $currentCount + 1;
                $column = "default_avatar_p{$slot}";
                $defaultAvatarId = $config->$column;
                
                $defaultAvatar = \App\Models\Avatar::find($defaultAvatarId);
                if ($defaultAvatar) {
                    $profileData['avatar'] = $defaultAvatar->image;
                } else {
                    $defaultAvatarFallback = \App\Models\Avatar::where('is_default', true)->first();
                    if ($defaultAvatarFallback) {
                        $profileData['avatar'] = $defaultAvatarFallback->image;
                    }
                }
            }
        }

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
        $user = $request->user();
        $profile = $user->profiles()->findOrFail($id);

        if ($this->isProfileLocked($user, $profile->id)) {
            return response()->json([
                'message' => 'Este perfil está bloqueado devido ao limite do seu plano atual. Renove seu VIP para acessá-lo.'
            ], 403);
        }

        return response()->json($profile);
    }

    /**
     * Atualiza um perfil.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profiles()->findOrFail($id);

        if ($this->isProfileLocked($user, $profile->id)) {
            return response()->json([
                'message' => 'Você não pode editar um perfil bloqueado. Renove seu VIP.'
            ], 403);
        }

        $validated = $request->validate([
            'name'    => ['sometimes', 'string', 'max:50'],
            'avatar'  => ['nullable', 'string', 'max:255'],
            'is_kids' => ['sometimes', 'boolean'],
            'pin'     => ['sometimes', 'nullable', 'string', 'size:4'],
            'is_adult_enabled' => ['sometimes', 'boolean'],
            'adult_pin' => ['sometimes', 'nullable', 'string', 'size:4']
        ]);

        if ($request->has('pin')) {
            $validated['pin'] = $request->pin;
        }

        if ($request->has('adult_pin')) {
            $validated['adult_pin'] = $request->adult_pin;
        }

        $profile->update($validated);
        $profile->refresh();

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

        if (!empty($profile->pin)) {
            $request->validate([
                'pin' => ['required', 'string', 'size:4']
            ]);

            if ((string) $profile->pin !== (string) $request->pin) {
                return response()->json([
                    'message' => 'PIN incorreto. Não é possível excluir o perfil.'
                ], 403);
            }
        }

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

        $user = $request->user();
        $profile = $user->profiles()->findOrFail($id);

        if ($this->isProfileLocked($user, $profile->id)) {
            return response()->json([
                'message' => 'Este perfil está bloqueado devido ao limite do seu plano atual. Renove seu VIP para acessá-lo.'
            ], 403);
        }

        if ((string) $profile->pin !== (string) $request->pin) {
            return response()->json(['message' => 'PIN incorreto. Tente novamente.'], 403);
        }

        return response()->json(['message' => 'Acesso autorizado.', 'profile_id' => $profile->id]);
    }

    /**
     * Verifica o PIN Adulto do perfil selecionado.
     * POST /api/profiles/{id}/verify-adult-pin
     */
    public function verifyAdultPin(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:4']
        ]);

        $user = $request->user();
        $profile = $user->profiles()->findOrFail($id);

        if ((string) $profile->adult_pin !== (string) $request->pin) {
            return response()->json(['message' => 'PIN Adulto incorreto.'], 403);
        }

        return response()->json(['message' => 'Acesso autorizado.', 'profile_id' => $profile->id]);
    }
}
