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

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TMDBController;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['admin','auth'])->prefix('dashzin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dash');

    Route::get('/tmdb', [TMDBController::class, 'index'])->name('tmdb');
    Route::get('/tmdb/search', [TMDBController::class, 'search']);
    Route::post('/tmdb/import', [TMDBController::class, 'import']);


    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/create', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/ban', [UserController::class, 'ban'])->name('ban');
        Route::post('/{user}/unban', [UserController::class, 'unban'])->name('unban');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('delete');
    });
    Route::prefix('sliders')->name('sliders.')->group(function () {
        Route::get('/', [SliderController::class, 'index'])->name('index');
        Route::get('/create', [SliderController::class, 'create'])->name('create');
        Route::post('/store', [SliderController::class, 'store'])->name('store');
        Route::delete('/{slider}', [SliderController::class, 'destroy'])->name('delete');
        Route::get('/search', [SliderController::class, 'search'])->name('search');
    });

    // Cupons VIP
    Route::resource('coupons', CouponController::class)->except(['show']);

    // Planos de Assinatura
    Route::resource('subscription-plans', SubscriptionPlanController::class)->except(['show']);

    // Configurações Globais
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Comentários Moderação
    Route::resource('comments', CommentController::class)->only(['index', 'destroy']);
    Route::put('comments/{comment}/toggle', [CommentController::class, 'toggleApproval'])->name('comments.toggle');

    // Pedidos TMDB Moderação
    Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
    Route::delete('requests/{request}', [RequestController::class, 'destroy'])->name('requests.destroy');
    Route::put('requests/{request}', [RequestController::class, 'updateStatus'])->name('requests.update');
    Route::post('requests/{request}/autoimport', [RequestController::class, 'autoImport'])->name('requests.autoimport');

    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [MovieController::class, 'index'])->name('index');
        Route::delete('/{movie}', [MovieController::class, 'destroy'])->name('delete');

        Route::get('/{movie}/links', [MovieController::class, 'links'])->name('links');
        Route::get('/{movie}/links/create', [MovieController::class, 'createLink'])->name('links.create');
        Route::post('/{movie}/links', [MovieController::class, 'storeLink'])->name('links.store');
        Route::get('/links/{link}/edit', [MovieController::class, 'editLink'])->name('links.edit');
        Route::put('/links/{link}', [MovieController::class, 'updateLink'])->name('links.update');
        Route::delete('/links/{link}', [MovieController::class, 'deleteLink'])->name('links.delete');
    });

    Route::prefix('series')->name('series.')->group(function () {
        Route::get('/', [SerieController::class, 'index'])->name('index');
        Route::delete('/{serie}', [SerieController::class, 'destroy'])->name('delete');
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
    Route::get('notifications/search-content', [\App\Http\Controllers\Admin\NotificationController::class, 'searchContent'])->name('notifications.search');
    Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class)->names('notifications');

    // ========== EVENTOS AO VIVO ==========
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class)->names('events');
    Route::get('events/{event}/links', [\App\Http\Controllers\Admin\EventController::class, 'links'])->name('events.links');
    Route::get('events/{event}/links/create', [\App\Http\Controllers\Admin\EventController::class, 'createLink'])->name('events.links.create');
    Route::post('events/{event}/links', [\App\Http\Controllers\Admin\EventController::class, 'storeLink'])->name('events.links.store');
    Route::get('event-links/{link}/edit', [\App\Http\Controllers\Admin\EventController::class, 'editLink'])->name('events.links.edit');
    Route::put('event-links/{link}', [\App\Http\Controllers\Admin\EventController::class, 'updateLink'])->name('events.links.update');
    Route::delete('event-links/{link}', [\App\Http\Controllers\Admin\EventController::class, 'deleteLink'])->name('events.links.destroy');

});


