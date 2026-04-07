<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\BannedDevice;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Registra um novo usuário e retorna token de acesso.
     *
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'username'              => ['required', 'string', 'max:255', 'unique:users'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'device_uuid'           => ['required', 'string'],
            'device_name'           => ['nullable', 'string'],
            'device_type'           => ['nullable', 'string'],
        ]);

        $this->checkDeviceBan($request->device_uuid);

        $user = User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Criar perfil padrão (Principal) para o novo usuário
        $user->profiles()->create([
            'name'    => 'Principal',
            'is_main' => true,
        ]);

        $tokenInstance = $user->createToken('api-token');
        $this->setDeviceMetadata($tokenInstance->accessToken, $request);
        $token = $tokenInstance->plainTextToken;

        return response()->json([
            'user'  => $user->refresh(),
            'token' => $token,
        ], 201);
    }

    /**
     * Autentica o usuário e retorna token de acesso.
     *
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
            'device_uuid' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string'],
        ]);

        $this->checkDeviceBan($request->device_uuid);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$loginField => $request->login, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'login' => ['Credenciais inválidas.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Revoga tokens antigos (opcional — mantém apenas 1 token ativo por vez)
        // $user->tokens()->delete();

        $tokenInstance = $user->createToken('api-token');
        $this->setDeviceMetadata($tokenInstance->accessToken, $request);
        $token = $tokenInstance->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Revoga o token atual do usuário.
     *
     * POST /api/auth/logout
     * Header: Authorization: Bearer <token>
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    /**
     * Retorna os dados do usuário autenticado.
     *
     * GET /api/auth/me
     * Header: Authorization: Bearer <token>
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Verifica se o dispositivo está banido.
     */
    private function checkDeviceBan(string $uuid): void
    {
        $ban = BannedDevice::where('device_uuid', $uuid)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($ban) {
            $msg = $ban->ban_reason ?: 'Este dispositivo foi banido da plataforma.';
            if ($ban->expires_at) {
                $msg .= ' Expira em: ' . $ban->expires_at->format('d/m/Y H:i');
            }

            abort(response()->json([
                'status'  => false,
                'message' => $msg,
                'error_code' => 'DEVICE_BANNED'
            ], 403));
        }
    }

    /**
     * Salva metadados no token.
     */
    private function setDeviceMetadata($token, Request $request): void
    {
        $ip = $request->ip();
        $location = 'Localhost';

        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            try {
                $response = Http::get("http://ip-api.com/json/{$ip}?fields=status,city,regionName,country");
                if ($response->successful() && $response['status'] === 'success') {
                    $location = $response['city'] . ' / ' . $response['regionName'] . ' (' . $response['country'] . ')';
                }
            } catch (\Exception $e) {
                $location = 'Localização Indisponível';
            }
        }

        $token->update([
            'device_uuid' => $request->device_uuid,
            'device_name' => $request->device_name ?: 'Dispositivo Desconhecido',
            'device_type' => $request->device_type ?: 'mobile',
            'ip_address'  => $ip,
            'user_agent'  => $request->userAgent(),
            'location'    => $location,
        ]);
    }
}
