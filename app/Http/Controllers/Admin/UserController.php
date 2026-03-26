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
            'is_admin' => 'required|boolean',
        ]);

        // Forçar username minúsculo
        $validated['username'] = strtolower($validated['username']);

        // Criação do usuário
        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'], // hashed automaticamente pelo cast do model
            'is_admin' => $validated['is_admin'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }
}