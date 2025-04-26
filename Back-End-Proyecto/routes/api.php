<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Rutas sin autenticación, son públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'reguster']);