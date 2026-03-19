<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Models\TipoDocumento;
use App\Models\Genero;
use App\Models\Departamento;
use App\Models\Municipio;


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me',       [AuthController::class, 'me']);

    
    Route::get('/paciente',                       [UserController::class, 'index']);
    Route::post('/paciente',                      [UserController::class, 'store']);
    Route::get('/paciente/{numero_documento}',    [UserController::class, 'show']);
    Route::put('/paciente/{numero_documento}',    [UserController::class, 'update']);
    Route::delete('/paciente/{numero_documento}', [UserController::class, 'destroy']);
    Route::post('/paciente/upload-foto', [UserController::class, 'uploadFoto']);

    
    Route::get('/tipos-documento', fn() => response()->json(['data' => TipoDocumento::all()]));
    Route::get('/generos',         fn() => response()->json(['data' => Genero::all()]));
    Route::get('/departamentos',   fn() => response()->json(['data' => Departamento::all()]));
    Route::get('/municipios',      fn() => response()->json(['data' => Municipio::all()]));
    
});