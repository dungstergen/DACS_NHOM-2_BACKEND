<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AmenityPublicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RoomPublicController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\RoomController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/rooms', [RoomPublicController::class, 'index']);
Route::get('/rooms/{room}', [RoomPublicController::class, 'show']);
Route::get('/amenities', [AmenityPublicController::class, 'index']);

Route::get('/posts', [\App\Http\Controllers\PostPublicController::class, 'index']);
Route::get('/posts/{slug}', [\App\Http\Controllers\PostPublicController::class, 'show']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::get('/contracts', [\App\Http\Controllers\RentalContractController::class, 'index']);
    Route::get('/contracts/{contract}', [\App\Http\Controllers\RentalContractController::class, 'show']);

    Route::get('/bills', [\App\Http\Controllers\MonthlyBillController::class, 'index']);
    Route::get('/bills/{bill}', [\App\Http\Controllers\MonthlyBillController::class, 'show']);

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

        Route::get('/appointments', [AdminAppointmentController::class, 'index']);
        Route::patch('/appointments/{appointment}', [AdminAppointmentController::class, 'update']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}', [AdminOrderController::class, 'update']);

        Route::get('/contracts', [\App\Http\Controllers\Admin\RentalContractController::class, 'index']);
        Route::post('/contracts', [\App\Http\Controllers\Admin\RentalContractController::class, 'store']);
        Route::get('/contracts/{contract}', [\App\Http\Controllers\Admin\RentalContractController::class, 'show']);
        Route::patch('/contracts/{contract}/status', [\App\Http\Controllers\Admin\RentalContractController::class, 'updateStatus']);

        Route::get('/settings/billing', [\App\Http\Controllers\Admin\BillingConfigController::class, 'show']);
        Route::put('/settings/billing', [\App\Http\Controllers\Admin\BillingConfigController::class, 'update']);

        Route::get('/bills', [\App\Http\Controllers\Admin\MonthlyBillController::class, 'index']);
        Route::post('/bills', [\App\Http\Controllers\Admin\MonthlyBillController::class, 'store']);
        Route::get('/bills/{bill}', [\App\Http\Controllers\Admin\MonthlyBillController::class, 'show']);
        Route::patch('/bills/{bill}/status', [\App\Http\Controllers\Admin\MonthlyBillController::class, 'updateStatus']);

        Route::get('/dashboard/summary', [\App\Http\Controllers\Admin\DashboardController::class, 'summary']);

        Route::get('/posts', [\App\Http\Controllers\Admin\PostController::class, 'index']);
        Route::post('/posts', [\App\Http\Controllers\Admin\PostController::class, 'store']);
        Route::get('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'show']);
        Route::put('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'update']);
        Route::patch('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'update']);
        Route::delete('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'destroy']);
    });
});
