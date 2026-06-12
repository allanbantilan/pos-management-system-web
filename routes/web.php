<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MayaCheckoutController;
use App\Http\Controllers\PosCheckoutController;
use App\Http\Controllers\PosItemController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/pos/checkout/callback/{transaction}/{result}', [MayaCheckoutController::class, 'callback'])
    ->whereIn('result', ['success', 'failed', 'cancelled'])
    ->name('pos.checkout.callback');

// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');

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
    Route::get('/pos/dashboard', [PosItemController::class, 'dashboard'])->name('pos.dashboard');

    // POS API endpoints
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/items', [PosItemController::class, 'getItems'])->name('items.index');
        Route::get('/items/{id}', [PosItemController::class, 'getItem'])->name('items.show');
        // Only users with the "can process sale" permission may complete a sale.
        // This blocks role-less self-registered accounts from creating transactions.
        Route::post('/checkout', [PosCheckoutController::class, 'checkout'])
            ->middleware(['can:can process sale', 'throttle:30,1'])
            ->name('checkout');
    });
});
