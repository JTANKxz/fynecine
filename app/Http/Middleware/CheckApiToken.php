<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        
        // Debug: Log the path
        \Log::info('CheckApiToken Path: ' . $request->path());

        // PULA TUDO DO PIX / MERCADO PAGO SEM ERRO
        $uri = $request->getRequestUri();
        if (str_contains($uri, 'mercadopago') || str_contains($uri, 'pix/status')) {
            return $next($request);
        }

        $token = $request->header('X-API-Token');
        $expectedToken = \App\Models\AppConfig::getSettings()->api_token_key;

        if ($token !== $expectedToken) {
            return response()->json([
                'status' => false,
                'message' => 'Acesso não autorizado. Chave de API inválida ou ausente.'
            ], 403);
        }

        return $next($request);
    }
}
