<?php

use App\Http\Controllers\CodingStatsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;

// ── Public Routes ──────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/coding-stats', [CodingStatsController::class, 'index'])
    ->middleware('throttle:coding-stats')
    ->name('coding-stats');

Route::get('/cv/download', [HomeController::class, 'downloadCv'])
    ->middleware('throttle:cv-download')
    ->name('cv.download');

// ── Auth Routes ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])
        ->name('admin.login');
    Route::post('/admin/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('admin.login.post');
});

Route::post('/admin/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

// ── Admin Routes (protected) ───────────────────────────────────
Route::middleware(['auth', EnsureAdminAuthenticated::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Landing Page Management
        Route::get('/landing', [\App\Http\Controllers\Admin\LandingPageController::class, 'index'])
            ->name('landing.index');
        Route::put('/landing/hero', [\App\Http\Controllers\Admin\LandingPageController::class, 'updateHero'])
            ->name('landing.hero.update');
        Route::put('/landing/social', [\App\Http\Controllers\Admin\LandingPageController::class, 'updateSocialLinks'])
            ->name('landing.social.update');
        Route::post('/landing/cv', [\App\Http\Controllers\Admin\LandingPageController::class, 'uploadCv'])
            ->name('landing.cv.upload');
        Route::delete('/landing/cv', [\App\Http\Controllers\Admin\LandingPageController::class, 'deleteCv'])
            ->name('landing.cv.delete');

        // Timeline
        Route::apiResource('timeline', \App\Http\Controllers\Admin\TimelineEntryController::class);

        // Skills
        Route::apiResource('skills', \App\Http\Controllers\Admin\SkillController::class);

        // Projects CRUD
        Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);

        // Messages
        Route::get('/messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])
            ->name('messages.index');
        Route::get('/messages/{message}', [\App\Http\Controllers\Admin\MessageController::class, 'show'])
            ->name('messages.show');
        Route::delete('/messages/{message}', [\App\Http\Controllers\Admin\MessageController::class, 'destroy'])
            ->name('messages.destroy');
    });
