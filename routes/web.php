<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\SerieController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\TvChannelController;
use App\Http\Controllers\Admin\TvChannelCategoryController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\NetworkController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\AvatarController;
use App\Http\Controllers\Admin\AvatarCategoryController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\InAppNotificationController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\ContentCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ChampionshipController;
use App\Http\Controllers\Admin\TMDBController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FrontendController;

// Rotas Frontend (Públicas)
Route::controller(FrontendController::class)->group(function() {
    Route::get('/', 'index')->name('home');
    Route::get('/movie/{slug}', 'movie')->name('frontend.movie');
    Route::get('/series/{slug}', 'serie')->name('frontend.serie');
    Route::get('/series/{slug}/season/{season}/episode/{episode}', 'episode')->name('frontend.episode');
    Route::get('/search', 'search')->name('frontend.search');
    Route::get('/genre/{slug}', 'genre')->name('frontend.genre');
    Route::get('/network/{slug}', 'network')->name('frontend.network');
});

// Rotas Públicas (Legal)
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Recuperação de Senha
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware(['admin','auth'])->prefix('dashzin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dash');

    Route::get('/tmdb', [TMDBController::class, 'index'])->name('tmdb');
    Route::get('/tmdb/search', [TMDBController::class, 'search']);
    Route::post('/tmdb/import', [TMDBController::class, 'import']);
    Route::get('/tmdb/seasons/{tmdbId}', [TMDBController::class, 'fetchSeasonsForSync'])->name('tmdb.seasons');
    Route::get('/tmdb/episodes/{tmdbId}/{seasonNumber}', [TMDBController::class, 'fetchEpisodesForSync'])->name('tmdb.episodes');
    Route::post('/tmdb/sync-seasons', [TMDBController::class, 'syncSeasons'])->name('tmdb.sync-seasons');
    Route::post('/tmdb/sync-episodes', [TMDBController::class, 'syncEpisodes'])->name('tmdb.sync-episodes');


    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/create', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/ban', [UserController::class, 'ban'])->name('ban');
        Route::post('/{user}/unban', [UserController::class, 'unban'])->name('unban');
        Route::post('/{user}/ban-device', [UserController::class, 'banDevice'])->name('ban-device');
        Route::post('/{user}/unban-device/{uuid}', [UserController::class, 'unbanDevice'])->name('unban-device');
        Route::post('/{user}/revoke-token/{token}', [UserController::class, 'revokeToken'])->name('revoke-token');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('delete');
    });
    Route::prefix('sliders')->name('sliders.')->group(function () {
        Route::get('/', [SliderController::class, 'index'])->name('index');
        Route::get('/create', [SliderController::class, 'create'])->name('create');
        Route::post('/store', [SliderController::class, 'store'])->name('store');
        Route::get('/{slider}/edit', [SliderController::class, 'edit'])->name('edit');
        Route::put('/{slider}', [SliderController::class, 'update'])->name('update');
        Route::delete('/{slider}', [SliderController::class, 'destroy'])->name('delete');
        Route::get('/search', [SliderController::class, 'search'])->name('search');
    });

    // Cupons VIP
    Route::resource('coupons', CouponController::class)->except(['show']);

    // Planos de Assinatura e Pagamentos PIX
    Route::resource('subscription-plans', SubscriptionPlanController::class)->except(['show']);
    Route::get('/pix-payments', [\App\Http\Controllers\Admin\PixPaymentController::class, 'index'])->name('pix-payments.index');

    // Configurações Globais
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Comentários Moderação
    Route::resource('comments', CommentController::class)->only(['index', 'destroy']);
    Route::put('comments/{comment}/toggle', [CommentController::class, 'toggleApproval'])->name('comments.toggle');

    Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
    Route::delete('requests/{request}', [RequestController::class, 'destroy'])->name('requests.destroy');
    Route::put('requests/{request}', [RequestController::class, 'updateStatus'])->name('requests.update');
    Route::post('requests/{request}/autoimport', [RequestController::class, 'autoImport'])->name('requests.autoimport');
    Route::post('requests/{request}/respond', [RequestController::class, 'respond'])->name('requests.respond');

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::patch('/{ticket}', [TicketController::class, 'update'])->name('update');
        Route::post('/{ticket}/respond', [TicketController::class, 'respond'])->name('respond');
        Route::delete('/{ticket}', [TicketController::class, 'delete'])->name('delete');
    });

    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [MovieController::class, 'index'])->name('index');
        Route::get('/bulk', [MovieController::class, 'bulkImport'])->name('bulk');
        Route::get('/bulk/ids', [MovieController::class, 'getBulkIds'])->name('bulk.ids');
        Route::post('/bulk/import', [MovieController::class, 'processImport'])->name('bulk.import');

        Route::delete('/{movie}', [MovieController::class, 'destroy'])->name('delete');
        Route::patch('/{movie}/category', [MovieController::class, 'updateCategory'])->name('category.update');
        Route::patch('/{movie}/tag', [MovieController::class, 'updateTag'])->name('tag.update');
        Route::patch('/{movie}/settings', [MovieController::class, 'updateSettings'])->name('settings.update');

        Route::get('/{movie}/links', [MovieController::class, 'links'])->name('links');
        Route::get('/{movie}/links/create', [MovieController::class, 'createLink'])->name('links.create');
        Route::post('/{movie}/links', [MovieController::class, 'storeLink'])->name('links.store');
        Route::get('/links/{link}/edit', [MovieController::class, 'editLink'])->name('links.edit');
        Route::put('/links/{link}', [MovieController::class, 'updateLink'])->name('links.update');
        Route::delete('/links/{link}', [MovieController::class, 'deleteLink'])->name('links.delete');
    });

    Route::prefix('series')->name('series.')->group(function () {
        Route::get('/', [SerieController::class, 'index'])->name('index');
        Route::get('/bulk', [SerieController::class, 'bulkImport'])->name('bulk');
        Route::get('/bulk/ids', [SerieController::class, 'getBulkIds'])->name('bulk.ids');
        Route::post('/bulk/import', [SerieController::class, 'processImport'])->name('bulk.import');

        Route::get('/bulk-anime', [SerieController::class, 'bulkAnimeImport'])->name('bulk.anime');
        Route::get('/bulk-anime/ids', [SerieController::class, 'getBulkAnimeIds'])->name('bulk.anime.ids');

        Route::delete('/{serie}', [SerieController::class, 'destroy'])->name('delete');
        Route::patch('/{serie}/category', [SerieController::class, 'updateCategory'])->name('category.update');
        Route::patch('/{serie}/tag', [SerieController::class, 'updateTag'])->name('tag.update');
        Route::patch('/{serie}/settings', [SerieController::class, 'updateSettings'])->name('settings.update');
        Route::get('/{serie}/seasons', [SerieController::class, 'seasons'])->name('seasons');
        Route::get('/seasons/{season}/episodes', [SerieController::class, 'episodes'])->name('episodes');
        Route::put('/seasons/{season}', [SerieController::class, 'updateSeason'])
            ->name('seasons.update');
        Route::put('/episodes/{episode}', [SerieController::class, 'updateEpisode'])->name('episodes.update');
        Route::delete('/episodes/{episode}', [SerieController::class, 'deleteEpisode'])
            ->name('episodes.delete');

        // LINKS DOS EPISÓDIOS
        Route::get('/episodes/{episode}/links', [SerieController::class, 'episodeLinks'])->name('episodes.links');
        Route::get('/episodes/{episode}/links/create', [SerieController::class, 'createEpisodeLink'])->name('episodes.links.create');
        Route::post('/episodes/{episode}/links', [SerieController::class, 'storeEpisodeLink'])->name('episodes.links.store');
        Route::get('/links/{link}/edit', [SerieController::class, 'editEpisodeLink'])->name('episodes.links.edit');
        Route::put('/links/{link}', [SerieController::class, 'updateEpisodeLink'])->name('episodes.links.update');
        Route::delete('/links/{link}', [SerieController::class, 'deleteEpisodeLink'])->name('episodes.links.delete');

    });

    // ========== UPCOMINGS ==========
    Route::prefix('upcomings')->name('upcomings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UpcomingController::class, 'index'])->name('index');
        Route::post('/import', [\App\Http\Controllers\Admin\UpcomingController::class, 'import'])->name('import');
        Route::delete('/{upcoming}', [\App\Http\Controllers\Admin\UpcomingController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('links')->name('links.')->group(function () {
        Route::get('/movies', [LinkController::class, 'movies'])->name('movies');
        Route::get('/series', [LinkController::class, 'series'])->name('series');
        Route::get('/series/{serie}/manage', [LinkController::class, 'serieManage'])->name('series.manage');
        
        Route::post('/movies/{movie}/store', [LinkController::class, 'storeMovieLink'])->name('movies.store');
        Route::put('/movies/link/{link}', [LinkController::class, 'updateMovieLink'])->name('movies.update');
        Route::delete('/movies/link/{link}', [LinkController::class, 'destroyMovieLink'])->name('movies.delete');

        Route::post('/series/episode/{episode}/store', [LinkController::class, 'storeEpisodeLink'])->name('series.episode.store');
        Route::put('/series/link/{link}', [LinkController::class, 'updateEpisodeLink'])->name('series.episode.update');
        Route::delete('/series/link/{link}', [LinkController::class, 'destroyEpisodeLink'])->name('series.episode.delete');
    });

    // ========== DOWNLOADS ==========
    Route::prefix('downloads')->name('downloads.')->group(function () {
        Route::get('/movies', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'movies'])->name('movies');
        Route::get('/series', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'series'])->name('series');
        Route::get('/series/{serie}/manage', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'serieManage'])->name('series.manage');

        Route::post('/movies/{movie}/store', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'storeMovieDownload'])->name('movies.store');
        Route::put('/movies/link/{link}', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'updateMovieDownload'])->name('movies.update');
        Route::delete('/movies/link/{link}', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'destroyMovieDownload'])->name('movies.delete');

        Route::post('/series/episode/{episode}/store', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'storeEpisodeDownload'])->name('series.episode.store');
        Route::put('/series/link/{link}', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'updateEpisodeDownload'])->name('series.episode.update');
        Route::delete('/series/link/{link}', [\App\Http\Controllers\Admin\DownloadLinkController::class, 'destroyEpisodeDownload'])->name('series.episode.delete');
    });

    // ========== TV AO VIVO ==========
    Route::prefix('channels')->name('channels.')->group(function () {
        Route::get('/', [TvChannelController::class, 'index'])->name('index');
        Route::get('/create', [TvChannelController::class, 'create'])->name('create');
        Route::post('/', [TvChannelController::class, 'store'])->name('store');
        Route::get('/{channel}/edit', [TvChannelController::class, 'edit'])->name('edit');
        Route::put('/{channel}', [TvChannelController::class, 'update'])->name('update');
        Route::delete('/{channel}', [TvChannelController::class, 'delete'])->name('delete');

        // Links do canal
        Route::get('/{channel}/links', [TvChannelController::class, 'links'])->name('links');
        Route::get('/{channel}/links/create', [TvChannelController::class, 'createLink'])->name('links.create');
        Route::post('/{channel}/links', [TvChannelController::class, 'storeLink'])->name('links.store');
        Route::get('/links/{link}/edit', [TvChannelController::class, 'editLink'])->name('links.edit');
        Route::put('/links/{link}', [TvChannelController::class, 'updateLink'])->name('links.update');
        Route::delete('/links/{link}', [TvChannelController::class, 'deleteLink'])->name('links.delete');
    });

    Route::prefix('channel-categories')->name('channel-categories.')->group(function () {
        Route::get('/', [TvChannelCategoryController::class, 'index'])->name('index');
        Route::get('/create', [TvChannelCategoryController::class, 'create'])->name('create');
        Route::post('/', [TvChannelCategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [TvChannelCategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [TvChannelCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [TvChannelCategoryController::class, 'delete'])->name('delete');
    });

    // ========== SEÇÕES DA HOME ==========
    Route::prefix('sections')->name('sections.')->group(function () {
        Route::get('/', [HomeSectionController::class, 'index'])->name('index');
        Route::get('/create', [HomeSectionController::class, 'create'])->name('create');
        Route::post('/', [HomeSectionController::class, 'store'])->name('store');
        Route::get('/{section}/edit', [HomeSectionController::class, 'edit'])->name('edit');
        Route::put('/{section}', [HomeSectionController::class, 'update'])->name('update');
        Route::delete('/{section}', [HomeSectionController::class, 'destroy'])->name('delete');
        
        Route::patch('/{section}/toggle', [HomeSectionController::class, 'toggle'])->name('toggle');
        Route::post('/reorder', [HomeSectionController::class, 'reorder'])->name('reorder');
        
        Route::get('/{section}/items', [HomeSectionController::class, 'items'])->name('items');
        Route::post('/{section}/items', [HomeSectionController::class, 'addItem'])->name('items.add');
        Route::delete('/items/{item}', [HomeSectionController::class, 'removeItem'])->name('items.remove');
        Route::get('/search', [HomeSectionController::class, 'searchContent'])->name('search');
    });

    // ========== NETWORKS ==========
    Route::prefix('networks')->name('networks.')->group(function () {
        Route::get('/', [NetworkController::class, 'index'])->name('index');
        Route::get('/create', [NetworkController::class, 'create'])->name('create');
        Route::post('/', [NetworkController::class, 'store'])->name('store');
        Route::get('/{network}/edit', [NetworkController::class, 'edit'])->name('edit');
        Route::put('/{network}', [NetworkController::class, 'update'])->name('update');
        Route::delete('/{network}', [NetworkController::class, 'destroy'])->name('delete');
        
        Route::get('/{network}/content', [NetworkController::class, 'content'])->name('content');
        Route::post('/{network}/content', [NetworkController::class, 'addContent'])->name('content.add');
        Route::delete('/{network}/content', [NetworkController::class, 'removeContent'])->name('content.remove');
        Route::get('/search', [NetworkController::class, 'searchContent'])->name('search');
    });

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::patch('/{ticket}', [TicketController::class, 'update'])->name('update');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('delete');
    });

    // ========== AVATARES ==========
    Route::resource('avatar-categories', AvatarCategoryController::class)->names('avatar-categories');
    Route::resource('avatars', AvatarController::class)->names('avatars');
    Route::resource('content-categories', ContentCategoryController::class)->names('categories')->parameters(['content-categories' => 'category']);
    
    // Rotas específicas de notificações ANTES do resource genérico
    Route::get('notifications/search-content', [\App\Http\Controllers\Admin\NotificationController::class, 'searchContent'])->name('notifications.search');
    Route::get('notifications/search-users', [\App\Http\Controllers\Admin\NotificationController::class, 'searchUser'])->name('notifications.search-users');
    Route::post('notifications/user/send', [\App\Http\Controllers\Admin\NotificationController::class, 'sendToUser'])->name('notifications.send-user');
    Route::post('notifications/content/send', [\App\Http\Controllers\Admin\NotificationController::class, 'sendToContent'])->name('notifications.send-content');
    
    // Resource genérico por ÚLTIMO
    Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class)->names('notifications');
    
    Route::resource('in-app-notifications', InAppNotificationController::class)->names('in-app-notifications');
    Route::resource('push-notifications', PushNotificationController::class)->names('push-notifications');

    // ========== EVENTOS AO VIVO ==========
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class)->names('events');
    Route::get('events/{event}/links', [\App\Http\Controllers\Admin\EventController::class, 'links'])->name('events.links');
    Route::get('events/{event}/links/create', [\App\Http\Controllers\Admin\EventController::class, 'createLink'])->name('events.links.create');
    Route::post('events/{event}/links', [\App\Http\Controllers\Admin\EventController::class, 'storeLink'])->name('events.links.store');
    Route::get('event-links/{link}/edit', [\App\Http\Controllers\Admin\EventController::class, 'editLink'])->name('events.links.edit');
    Route::put('event-links/{link}', [\App\Http\Controllers\Admin\EventController::class, 'updateLink'])->name('events.links.update');
    Route::delete('event-links/{link}', [\App\Http\Controllers\Admin\EventController::class, 'deleteLink'])->name('events.links.destroy');

    // ========== TIMES / EQUIPES ==========
    Route::resource('teams', \App\Http\Controllers\Admin\TeamController::class)->names('teams');
    Route::get('teams-search', [\App\Http\Controllers\Admin\TeamController::class, 'search'])->name('teams.search');

    // ========== CAMPEONATOS ==========
    Route::resource('championships', ChampionshipController::class)->except(['show', 'create', 'edit']);

    // ========== ANOTAÇÕES ==========
    Route::prefix('notes')->name('notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminNoteController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\AdminNoteController::class, 'store'])->name('store');
        Route::put('/{note}', [\App\Http\Controllers\Admin\AdminNoteController::class, 'update'])->name('update');
        Route::delete('/{note}', [\App\Http\Controllers\Admin\AdminNoteController::class, 'destroy'])->name('destroy');
        Route::patch('/{note}/pin', [\App\Http\Controllers\Admin\AdminNoteController::class, 'togglePin'])->name('pin');
        Route::patch('/{note}/task', [\App\Http\Controllers\Admin\AdminNoteController::class, 'toggleTask'])->name('toggle-task');
    });

});

// PIX Checkout (página pública para WebView do app)
Route::get('/pix/checkout/{pixPaymentId}', [\App\Http\Controllers\PixCheckoutController::class, 'show'])->name('pix.checkout');
