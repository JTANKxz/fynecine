<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não estiver logado ou não for admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Acesso negado'); // ou redirect('/'); se preferir redirecionar
        }

        // Usuário é admin, segue
        return $next($request);
    }
}