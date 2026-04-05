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
Route::post('/fcm/register', [\App\Http\Controllers\Api\FcmDeviceController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Rotas Públicas de Conteúdo
|--------------------------------------------------------------------------
*/
Route::get('/teste', [TestController::class, 'index']);

Route::get('/home', [HomeController::class, 'index']);
Route::get('/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
Route::get('/sections/page/{slug}', [HomeController::class, 'categoryPage']);
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

// Watch Progress (Continuar Assistindo) - Público para guest_id
Route::prefix('progress')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\WatchProgressController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\Api\WatchProgressController::class, 'index']);
    Route::get('/{contentId}', [\App\Http\Controllers\Api\WatchProgressController::class, 'show']);
});

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

    // Suporte (Tickets) protegidos
    Route::get('/tickets/topics', [\App\Http\Controllers\Api\TicketController::class, 'getTopics']);
    Route::post('/tickets', [\App\Http\Controllers\Api\TicketController::class, 'store']);

    // Downloads — registro e controle de limite diário
    Route::post('/downloads/log', [\App\Http\Controllers\Api\DownloadController::class, 'log']);
    Route::get('/downloads/status', [\App\Http\Controllers\Api\DownloadController::class, 'status']);

    // Watch Progress (Continuar Assistindo) - Delete requer auth
    Route::prefix('progress')->group(function () {
        Route::delete('/{contentId}', [\App\Http\Controllers\Api\WatchProgressController::class, 'destroy']);
        Route::delete('/', [\App\Http\Controllers\Api\WatchProgressController::class, 'destroyAll']);
    });

    // Notificações - Ações que requerem Auth
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{id}/markread', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
});

// Notificações — Listagem Pública (Internamente decide se mostra globais ou privadas)
Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);

// PIX Checkout (Mercado Pago)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pix/create', [\App\Http\Controllers\Api\PixPaymentController::class, 'create']);
});

// Status público (consumido pelo JS da página de checkout)
Route::get('/pix/status/{paymentId}', [\App\Http\Controllers\Api\PixPaymentController::class, 'status']);

// Webhook do Mercado Pago (público — sem auth)
Route::post('/webhooks/mercadopago', [\App\Http\Controllers\Api\PixWebhookController::class, 'handle']);