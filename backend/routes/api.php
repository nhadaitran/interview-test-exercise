<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/user', function (Request $request) {
            return response()->json($request->user());
        });

        Route::get('/users', [UserController::class, 'index']);

        Route::apiResource('equipment', EquipmentController::class);
        Route::apiResource('reservations', ReservationController::class);
    });
});
