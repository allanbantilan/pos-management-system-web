<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Register
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    // Password Reset
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // POS Dashboard
    Route::get('/pos/dashboard', [POSController::class, 'dashboard'])->name('pos.dashboard');

    // POS API endpoints
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/products', [POSController::class, 'getProducts'])->name('products.index');
        Route::get('/products/{id}', [POSController::class, 'getProduct'])->name('products.show');
        Route::post('/checkout', [POSController::class, 'checkout'])->name('checkout');
    });
});

// API routes for AJAX calls
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Additional API endpoints can be added here
});
