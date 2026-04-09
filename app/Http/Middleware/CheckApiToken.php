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
        // 1. PERMITE OPTIONS (CORS) SEMPRE
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $uri = $request->getRequestUri();

        // 2. PULA TUDO DO PIX / MERCADO PAGO E LINKS DE PLAY SEM ERRO
        if (str_contains($uri, 'mercadopago') || str_contains($uri, 'pix/status') || str_contains($uri, 'links/')) {
            return $next($request);
        }

        // 3. VERIFICA TOKEN (Tratamento robusto)
        $token = trim($request->header('X-API-Token') ?? '');
        $expectedToken = trim(\App\Models\AppConfig::getSettings()->api_token_key ?? '');

        if (empty($expectedToken) || $token !== $expectedToken) {
            \Log::warning("CheckApiToken Falhou: Recebido '{$token}', Esperado '{$expectedToken}' para URI: " . $request->path());
            
            return response()->json([
                'status' => false,
                'message' => 'Acesso não autorizado. Chave de API inválida ou ausente.'
            ], 403);
        }

        return $next($request);
    }
}
