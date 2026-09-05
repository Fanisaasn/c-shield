<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Admin\FlyerController as AdminFlyerController;
use App\Http\Controllers\Admin\WebinarController as AdminWebinarController;
use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FlyerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SelfAssessmentController;
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

Route::prefix('self-assessment')->name('self-assessment.')->group(function () {
    Route::get('/', [SelfAssessmentController::class, 'themes'])->name('themes');
    Route::get('/pre', [SelfAssessmentController::class, 'preQuiz'])->name('pre.quiz');
    Route::post('/pre', [SelfAssessmentController::class, 'storePreQuiz'])->name('pre.store');
    Route::get('/pre/hasil', [SelfAssessmentController::class, 'preResult'])->name('pre.result');
    Route::get('/{category:slug}', [SelfAssessmentController::class, 'create'])->name('create');
    Route::post('/{category:slug}', [SelfAssessmentController::class, 'store'])->name('store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('articles', AdminArticleController::class)->parameters(['articles' => 'item'])->except('show');
        Route::resource('videos', AdminVideoController::class)->parameters(['videos' => 'item'])->except('show');
        Route::resource('flyers', AdminFlyerController::class)->parameters(['flyers' => 'item'])->except('show');
        Route::resource('webinars', AdminWebinarController::class)->parameters(['webinars' => 'item'])->except('show');
        Route::get('assessments', [AdminAssessmentController::class, 'index'])->name('assessments.index');
        Route::post('assessments/categories', [AdminAssessmentController::class, 'storeCategory'])->name('assessments.categories.store');
        Route::put('assessments/categories/{category}', [AdminAssessmentController::class, 'updateCategory'])->name('assessments.categories.update');
        Route::delete('assessments/categories/{category}', [AdminAssessmentController::class, 'destroyCategory'])->name('assessments.categories.destroy');
        Route::post('assessments/questions', [AdminAssessmentController::class, 'storeQuestion'])->name('assessments.questions.store');
        Route::put('assessments/questions/{question}', [AdminAssessmentController::class, 'updateQuestion'])->name('assessments.questions.update');
        Route::delete('assessments/questions/{question}', [AdminAssessmentController::class, 'destroyQuestion'])->name('assessments.questions.destroy');
        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    });
});
