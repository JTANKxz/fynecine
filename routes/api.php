<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileListController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SerieController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NetworkController;
use App\Http\Controllers\Api\HomeSectionController;
use App\Http\Controllers\Api\AvatarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booting (Público)
|--------------------------------------------------------------------------
*/
Route::get('/settings', [\App\Http\Controllers\Api\ConfigController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Rotas Públicas de Conteúdo
|--------------------------------------------------------------------------
*/
Route::get('/teste', [TestController::class, 'index']);

Route::get('/home', [HomeController::class, 'index']);
Route::get('/series', [SerieController::class, 'index']);
Route::get('/series/{idOrSlug}', [SerieController::class, 'show']);
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{idOrSlug}', [MovieController::class, 'show']);
Route::get('/{type}/{idOrSlug}/comments', [CommentController::class, 'index'])->where('type', 'movies|series');
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{idOrSlug}', [GenreController::class, 'show']);
Route::get('/search', [SearchController::class, 'index']);

// TV ao Vivo (Canais)
Route::get('/channels', [\App\Http\Controllers\Api\TvChannelController::class, 'index']);
Route::get('/channels/categories', [\App\Http\Controllers\Api\TvChannelController::class, 'categories']);
Route::get('/channels/{idOrSlug}', [\App\Http\Controllers\Api\TvChannelController::class, 'show']);

// Eventos Ao Vivo
Route::get('/events', [\App\Http\Controllers\Api\EventController::class, 'index']);
Route::get('/events/{id}', [\App\Http\Controllers\Api\EventController::class, 'show']);

// Planos de Assinatura (Para página de Pricing/Vendas)
Route::get('/plans', [\App\Http\Controllers\Api\SubscriptionController::class, 'plans']);

// Networks
Route::get('/networks', [NetworkController::class, 'index']);
Route::get('/networks/{idOrSlug}', [NetworkController::class, 'show']);

// Custom Home Sections (View All)
Route::get('/sections/{id}', [HomeSectionController::class, 'show']);

// Avatares
Route::get('/avatars', [AvatarController::class, 'index']);

// Views tracking
Route::post('/views', [\App\Http\Controllers\Api\ContentViewController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Autenticação API (Sanctum — Bearer Token)
|--------------------------------------------------------------------------
*/
// Rotas públicas de auth (não requerem token)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Rotas protegidas por token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Perfis (Netflix Style - Max 5)
    Route::apiResource('profiles', ProfileController::class);
    Route::post('/profiles/{id}/verify-pin', [ProfileController::class, 'verifyPin']);

    // Conta (Titular)
    Route::post('/account/update', [\App\Http\Controllers\Api\AccountController::class, 'update']);

    // Lista do Perfil (Watchlist)
    // OBS: O cliente DEVE enviar o header `Profile-Id: {id}`
    Route::prefix('list')->group(function () {
        Route::get('/',        [ProfileListController::class, 'index']);
        Route::post('/toggle', [ProfileListController::class, 'toggle']);
        Route::get('/check',   [ProfileListController::class, 'check']);
        Route::delete('/{id}', [ProfileListController::class, 'destroy']);
    });

    // Assinaturas e Cupons
    Route::post('/subscription/redeem', [SubscriptionController::class, 'redeem']);

    // Comentários
    Route::post('/{type}/{idOrSlug}/comments', [CommentController::class, 'store'])->whereIn('type', ['movies', 'series']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Pedidos (Requests) protegidos
    Route::get('/requests/search', [\App\Http\Controllers\Api\RequestController::class, 'search']);
    Route::post('/requests', [\App\Http\Controllers\Api\RequestController::class, 'store']);

    // Downloads — registro e controle de limite diário
    Route::post('/downloads/log', [\App\Http\Controllers\Api\DownloadController::class, 'log']);
    Route::get('/downloads/status', [\App\Http\Controllers\Api\DownloadController::class, 'status']);

    // Notificações
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
});