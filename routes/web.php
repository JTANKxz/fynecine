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

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::patch('/{ticket}', [TicketController::class, 'update'])->name('update');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('delete');
    });

});


