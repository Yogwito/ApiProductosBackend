<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoBaseController;
use App\Models\User;



// Publico
Route::post('login',[AuthController::class, 'login']);
Route::post('verify-code', [AuthController::class, 'verifyCode']);

//Autenticadas
Route::middleware('auth:api')->group(function () {

    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);

    Route::get('productos', [ProductoBaseController::class, 'index'])->middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_USUARIO . ',' . User::ROLE_OPERADOR);
    Route::post('productos', [ProductoBaseController::class, 'store'])->middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_OPERADOR);
    Route::get('productos/{id}', [ProductoBaseController::class, 'show'])->middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_USUARIO . ',' . User::ROLE_OPERADOR);
    Route::put('productos/{id}', [ProductoBaseController::class, 'update'])->middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_OPERADOR);
    Route::delete('productos/{id}', [ProductoBaseController::class, 'destroy'])->middleware('role:' . User::ROLE_ADMIN);
});
