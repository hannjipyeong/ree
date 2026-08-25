<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::post('/login', [ApiController::class, 'login']);
// Self-registration disabled — customers are created by admin via web dashboard only.
// Route::post('/register', [ApiController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if ($user->role === 'customer') {
            $user->load(['orders.containers', 'orders.subTasks.supir']);
        }
        return $user;
    });
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::get('/orders', [ApiController::class, 'getOrders']);
    Route::post('/orders', [ApiController::class, 'createOrder']);
    Route::patch('/sub-tasks/{id}/action', [ApiController::class, 'updateSubTaskAction']);

    Route::get('/notifications', [ApiController::class, 'notifications']);
    Route::get('/notifications/summary', [ApiController::class, 'notificationSummary']);
    Route::post('/notifications/mark-read', [ApiController::class, 'markNotificationsRead']);
});
