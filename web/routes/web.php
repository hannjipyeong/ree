<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupirController;

// Guest Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');

    // 1. Monitoring & CRUD Request
    Route::resource('requests', RequestController::class);
    Route::post('requests/{request}/update-services', [RequestController::class, 'updateServices'])->name('requests.updateServices');
    Route::get('requests/{request}/containers/{container}', [RequestController::class, 'showContainer'])->name('requests.containers.show');
    Route::post('requests/{request}/containers/{container}/update-services', [RequestController::class, 'updateContainerServices'])->name('requests.containers.updateServices');
    Route::patch('sub-tasks/{subTask}/status', [RequestController::class, 'updateSubTaskStatus'])->name('subtasks.updateStatus');
    Route::get('requests/{request}/export-pdf', [RequestController::class, 'exportPdf'])->name('requests.exportPdf');

    // 2. Monitoring & CRUD Akun Customer
    Route::resource('customers', UserController::class);

    // 3. Monitoring & CRUD Akun Supir
    Route::resource('supir', SupirController::class);
});

