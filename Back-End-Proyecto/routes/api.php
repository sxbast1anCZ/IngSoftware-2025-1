<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorAvailabilityController;


//Rutas sin autenticación, son públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);
Route::post('register2', [AuthController::class, 'registerDoctor']);

//Rutas para generar citas médicas
Route::middleware('auth:api')->post('/appointments', [AuthController::class, 'scheduleAppointment']);
Route::middleware('auth:api')->get('/appointments', [AuthController::class, 'getAppointments']);

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

//Rutas para gestionar la disponibilidad de un médico
Route::middleware('auth:api')->group(function () {
    Route::get('/doctor/disponibilidad', [DoctorAvailabilityController::class, 'index']);
    Route::put('/doctor/disponibilidad', [DoctorAvailabilityController::class, 'update']);
    Route::middleware('auth:api')->get('/doctor/citas', [DoctorAvailabilityController::class, 'citas']);
    Route::middleware('auth:api')->put('/doctor/disponibilidad/desactivar', [DoctorAvailabilityController::class, 'desactivarBloques']);
    Route::middleware('auth:api')->put('/doctor/disponibilidad/activar', [DoctorAvailabilityController::class, 'activarBloques']);
    Route::middleware(['auth:api'])->post('/paciente/doctor/disponibilidad', [DoctorAvailabilityController::class, 'verDisponibilidadMedicoPorNombre']);

});

  /* Ejemplo del body de POSTMAN de "Activar Bloque
    * Tipo de Usuario = Medico
    * Body:
    {
  "bloques": [
    {
      "dia_semana": 1,
      "hora_inicio": "08:00",
      "hora_fin": "10:00"
    }
  ]
}
    */

