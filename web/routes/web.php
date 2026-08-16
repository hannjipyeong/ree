<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupirController;
use App\Http\Controllers\ApiController;

// Guest Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 1. Monitoring & CRUD Request
    Route::resource('requests', RequestController::class);
    Route::patch('sub-tasks/{subTask}/status', [RequestController::class, 'updateSubTaskStatus'])->name('subtasks.updateStatus');

    // 2. Monitoring & CRUD Akun Customer
    Route::resource('customers', UserController::class);

    // 3. Monitoring & CRUD Akun Supir
    Route::resource('supir', SupirController::class);
});

// REST API Routes (for Mobile Flutter App)
Route::prefix('api')->group(function () {
    Route::post('/login', [ApiController::class, 'login']);
    Route::get('/orders', [ApiController::class, 'getOrders']);
    Route::post('/orders', [ApiController::class, 'createOrder']);
    Route::patch('/sub-tasks/{id}/action', [ApiController::class, 'updateSubTaskAction']);
});
