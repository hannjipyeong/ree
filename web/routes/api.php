<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::post('/login', [ApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::get('/orders', [ApiController::class, 'getOrders']);
    Route::post('/orders', [ApiController::class, 'createOrder']);
    Route::patch('/sub-tasks/{id}/action', [ApiController::class, 'updateSubTaskAction']);
});
