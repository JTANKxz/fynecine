<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // tela de login
    public function login()
    {
        return view('login.index');
    }

    // processa login
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            // verifica se é admin
            if (!$user->is_admin) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Credenciais inválidas'
                ]);
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dash');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.'
        ]);
    }

    // logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}