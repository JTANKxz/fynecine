<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Atualiza dados sensíveis da conta e (opcionalmente) o PIN do Titular.
     * Exige que o "Profile-Id" no Header pertença ao perfil principal (is_main = true).
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $profileId = $request->header('Profile-Id');

        if (!$profileId) {
            return response()->json(['message' => 'Acesso Negado: Selecione o Perfil Titular na interface para continuar.'], 403);
        }

        $profile = $user->profiles()->find($profileId);

        if (!$profile || !$profile->is_main) {
            return response()->json(['message' => 'Permissão Negada: Apenas o Perfil Titular pode alterar esses dados da conta.'], 403);
        }

        $request->validate([
            'email' => ['nullable', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'pin' => ['nullable', 'string', 'size:4'],
        ]);

        // Evitando atualizar os campos se estiverem vazios na requisição (só troca o que foi enviado de fato)
        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Se mandou PIN explicitamente, mesmo que para limpar (null/empty str), atualiza o PIN do perfil titular.
        if ($request->has('pin')) {
            $profile->pin = $request->pin;
            $profile->save();
        }

        return response()->json(['message' => 'Conta atualizada com sucesso.']);
    }
}
