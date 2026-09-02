<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FlyerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\WebinarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/video', [VideoController::class, 'index'])->name('videos.index');
Route::get('/video/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

Route::get('/flyer', [FlyerController::class, 'index'])->name('flyers.index');
Route::get('/flyer/{flyer}', [FlyerController::class, 'show'])->name('flyers.show');

Route::get('/webinar', [WebinarController::class, 'index'])->name('webinars.index');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    });
});
