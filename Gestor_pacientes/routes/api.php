<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con JWT
Route::middleware('auth:api')->group(function () {
    Route::post('/logout',         [AuthController::class, 'logout']);
    Route::post('/refresh',        [AuthController::class, 'refresh']);
    Route::get('/me',              [AuthController::class, 'me']);
   // Route::apiResource('paciente', UserController::class);


    Route::get('/paciente',                        [UserController::class, 'index']);
    Route::post('/paciente',                       [UserController::class, 'store']);
    Route::get('/paciente/{numero_documento}',     [UserController::class, 'show']);
    Route::put('/paciente/{numero_documento}',     [UserController::class, 'update']);
    Route::delete('/paciente/{numero_documento}',  [UserController::class, 'destroy']);
});