<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

// Publico
Route::post('login', [AuthController::class, 'login']);
Route::post('verify-code', [AuthController::class, 'verifyCode']);

// Autenticadas
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);

    Route::get('productos', [ProductoController::class, 'index'])->middleware('role:admin,usuario,operador');
    Route::post('productos', [ProductoController::class, 'store'])->middleware('role:admin,operador');
    Route::get('productos/{id}', [ProductoController::class, 'show'])->middleware('role:admin,usuario,operador');
    Route::put('productos/{id}', [ProductoController::class, 'update'])->middleware('role:admin,operador');
    Route::delete('productos/{id}', [ProductoController::class, 'destroy'])->middleware('role:admin');
});
