<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

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

            // verifica se tem acesso (admin ou editor)
            if (!$user->hasAdminPanelAccess()) {
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

    // --- RECUPERAÇÃO DE SENHA ---

    public function showForgotPassword()
    {
        return view('login.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link de recuperação enviado para o seu e-mail!')
            : back()->withErrors(['email' => 'Não conseguimos encontrar um usuário com esse endereço de e-mail.']);
    }

    public function showResetPassword($token)
    {
        return view('login.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only(['email', 'password', 'password_confirmation', 'token']),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Sua senha foi redefinida com sucesso!')
            : back()->withErrors(['email' => [__($status)]]);
    }
}