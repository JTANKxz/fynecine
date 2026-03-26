<?php

use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SerieController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TestController;
use Illuminate\Support\Facades\Route;


    Route::get('/teste', [TestController::class, 'index']);

    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/series', [SerieController::class, 'index']);
    Route::get('/series/{idOrSlug}', [SerieController::class, 'show']);
    Route::get('/movies', [MovieController::class, 'index']);
    Route::get('/movies/{idOrSlug}', [MovieController::class, 'show']);
    Route::get('/genres/{slug}', [GenreController::class, 'show']);
    Route::get('/search', [SearchController::class, 'index']);