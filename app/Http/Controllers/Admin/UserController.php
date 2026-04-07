<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Busca por id, name ou email
        if ($search = $request->input('search')) {
            $query->where('id', $search)
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        // Paginação
        $users = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        // Validação dos campos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,editor,user',
        ]);

        // Forçar username minúsculo
        $validated['username'] = strtolower($validated['username']);

        // Criação do usuário
        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'], // hashed automaticamente pelo cast do model
            'role' => $validated['role'],
            'is_admin' => $validated['role'] === 'admin',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }
    public function edit(User $user)
    {
        $tokens = $user->tokens()->orderBy('last_used_at', 'desc')->get();
        // Buscar UUIDs banidos
        $bannedUuids = \App\Models\BannedDevice::pluck('device_uuid')->toArray();
        
        return view('admin.users.edit', compact('user', 'tokens', 'bannedUuids'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'nullable|in:admin,editor,user',
            'plan_type' => 'required|in:free,basic,premium',
            'password' => 'nullable|string|min:6',
        ]);

        $currentUser = $request->user();

        // Editor não pode alterar SENHA nem CARGO
        if (!$currentUser->canChangeUserSensitiveData()) {
            unset($validated['password'], $validated['role']);
        }

        if (isset($validated['password'])) {
            $user->password = $validated['password'];
        }

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
            $user->is_admin = $validated['role'] === 'admin';
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->plan_type = $validated['plan_type'];
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function ban(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Ação permitida apenas para administradores.');
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $request->input('reason', 'Violação dos termos')
        ]);

        // Registrar o IP e Device ID para bloqueio total
        if ($user->last_login_ip) {
            \App\Models\BannedDevice::updateOrCreate(
                ['ip_address' => $user->last_login_ip],
                ['reason' => "Banimento total do usuário {$user->name}"]
            );
        }

        // Tenta banir todos os UUIDs associados aos tokens deste usuário
        foreach ($user->tokens as $token) {
            if ($token->device_uuid) {
                \App\Models\BannedDevice::updateOrCreate(
                    ['device_uuid' => $token->device_uuid],
                    ['reason' => "Banimento por associação à conta de {$user->name}"]
                );
            }
            $token->delete();
        }

        return back()->with('success', 'Usuário e todos os seus dispositivos foram banidos.');
    }

    /**
     * Bane um dispositivo específico por UUID.
     */
    public function banDevice(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Ação permitida apenas para administradores.');
        }

        $request->validate([
            'device_uuid' => 'required|string',
            'reason' => 'nullable|string'
        ]);

        \App\Models\BannedDevice::updateOrCreate(
            ['device_uuid' => $request->device_uuid],
            [
                'reason' => $request->reason ?: "Banimento manual via Admin para usuário: {$user->name}",
                'expires_at' => null // Permanente por padrão no admin
            ]
        );

        // Deleta todos os tokens que usam esse UUID para expulsar imediatamente
        \App\Models\PersonalAccessToken::where('device_uuid', $request->device_uuid)->delete();

        return back()->with('success', 'Dispositivo banido com sucesso e sessões encerradas.');
    }

    /**
     * Remove o UUID da lista negra.
     */
    public function unbanDevice(Request $request, User $user, $uuid)
    {
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Ação permitida apenas para administradores.');
        }

        \App\Models\BannedDevice::where('device_uuid', $uuid)->delete();

        return back()->with('success', 'Dispositivo desbloqueado com sucesso.');
    }

    /**
     * Revoga apenas um token específico (Desconectar dispositivo).
     */
    public function revokeToken(Request $request, User $user, $tokenId)
    {
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Ação permitida apenas para administradores.');
        }

        $user->tokens()->where('id', $tokenId)->delete();
        return back()->with('success', 'Dispositivo desconectado com sucesso.');
    }

    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null
        ]);

        return back()->with('success', 'Banimento removido.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }
}