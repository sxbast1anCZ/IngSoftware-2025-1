<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//Rutas sin autenticación, son públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);
