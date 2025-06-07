<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//Rutas sin autenticación, son públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);
Route::post('register2', [AuthController::class, 'registerDoctor']);

// Rutas protegidas para usuarios autenticados y habilitados
Route::middleware(['is.auth', 'is.enabled'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::put('me/updateUser', [AuthController::class, 'updateMe']); 
});

// Rutas exclusivas para administradores
Route::middleware(['is.auth', 'is.admin', 'is.enabled'])->group(function () {
    Route::get('admin/users', [AuthController::class, 'listUsers']);
    Route::put('admin/users/{id}/toggle', [AuthController::class, 'toggleUserStatus']);
    Route::put('users/{id}', [AuthController::class, 'updateUser']); // admin actualiza otro usuario
});
