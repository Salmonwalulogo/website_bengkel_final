<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('api.profile.update');

    Route::middleware('role:admin,kasir,customer')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

        Route::get('/vehicles', [VehicleController::class, 'index'])->name('api.vehicles.index');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('api.vehicles.store');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('api.vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('api.vehicles.destroy');

        Route::get('/services', [ServiceController::class, 'index'])->name('api.services.index');
        Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');

        Route::get('/bookings', [BookingController::class, 'index'])->name('api.bookings.index');
        Route::post('/bookings', [BookingController::class, 'store'])->name('api.bookings.store');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('api.transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('api.transactions.show');

        Route::post('/sync', [SyncController::class, 'batchSync'])->name('api.sync');
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('api.categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
        Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');
        Route::post('/services', [ServiceController::class, 'store'])->name('api.services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('api.services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('api.services.destroy');
        Route::post('/users', [UserController::class, 'store'])->name('api.users.store');
    });

    Route::middleware('role:admin,kasir')->group(function () {
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('api.bookings.status');
        Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
        Route::get('/payments', [PaymentController::class, 'index'])->name('api.payments.index');
    });

    Route::middleware('role:admin,kasir,customer')
        ->post('/transactions', [TransactionController::class, 'store'])
        ->name('api.transactions.store');

    Route::middleware('role:customer')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('api.notifications.read');
    });
});

// Products public or authenticated
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');
