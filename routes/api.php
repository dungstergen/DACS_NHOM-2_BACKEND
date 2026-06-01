<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AmenityPublicController;
use App\Http\Controllers\RoomPublicController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\RoomController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/rooms', [RoomPublicController::class, 'index']);
Route::get('/rooms/{room}', [RoomPublicController::class, 'show']);
Route::get('/amenities', [AmenityPublicController::class, 'index']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/rooms', [RoomController::class, 'index']);
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::get('/rooms/{room}', [RoomController::class, 'show']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::patch('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
        Route::patch('/rooms/{room}/status', [RoomController::class, 'updateStatus']);

        Route::get('/amenities', [AmenityController::class, 'index']);
        Route::post('/amenities', [AmenityController::class, 'store']);
        Route::put('/amenities/{amenity}', [AmenityController::class, 'update']);
        Route::patch('/amenities/{amenity}', [AmenityController::class, 'update']);
        Route::delete('/amenities/{amenity}', [AmenityController::class, 'destroy']);
    });
});
