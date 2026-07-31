<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GempaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No authentication required)
|--------------------------------------------------------------------------
*/

// Main application - Earthquake Risk Analysis
Route::get('/', [GempaController::class, 'index'])->name('home');
Route::post('/hitung', [GempaController::class, 'calculate'])->name('calculate');
Route::post('/unduh-laporan', [GempaController::class, 'downloadReport'])->name('download.report');

/*
|--------------------------------------------------------------------------
| API Routes (Public)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    Route::post('/hitung', [GempaController::class, 'apiCalculate'])->name('api.calculate');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Requires authentication)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin login (public)
    Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.post');

    // Protected admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [GempaController::class, 'history'])->name('history');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    });
});
