<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\AppointmentController;


//Rutas sin autenticación, son públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);
Route::post('register2', [AuthController::class, 'registerDoctor']);
Route::post('/citas/agendar', [AppointmentController::class, 'agendarCita']);
//Rutas de listado para FRONT END
Route::get('/especialidades/medicos', [SpecialtyController::class, 'obtenerEspecialidadesMedicos']);
Route::get('/medicos/publicos', [AuthController::class, 'listarMedicosPublicos']);
Route::get('/frontend/medicos', [AppointmentController::class, 'listarMedicosPublicos']);
Route::get('/especialidades', [SpecialtyController::class, 'getAllSpecialties']);
Route::get('/medicos/listado-completo', [AppointmentController::class, 'listarMedicosCompleto']);

//Rutas para generar citas médicas
Route::middleware('auth:api')->post('/appointments', [AppointmentController::class, 'scheduleAppointment']);
Route::middleware('auth:api')->get('/appointments', [AppointmentController::class, 'citasPaciente']);

// Rutas protegidas para usuarios autenticados y habilitados
Route::middleware(['is.auth', 'is.enabled'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::put('me/updateUser', [AuthController::class, 'updateMe']); 
    Route::get('/medicos/reserva', [AppointmentController::class, 'obtenerDatosMedicoReserva']);
    Route::get('/medicos/disponibilidad', [AppointmentController::class, 'obtenerDisponibilidadMedicoPorFecha']);
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
    Route::post('/doctor/disponibilidad', [DoctorAvailabilityController::class, 'crearDisponibilidadMedico']);
    Route::put('/doctor/disponibilidad', [DoctorAvailabilityController::class, 'actualizarDisponibilidadMedico']);
    Route::middleware('auth:api')->get('/doctor/citas', [DoctorAvailabilityController::class, 'citas']);
    Route::post('/doctor/disponibilidad/desactivar', [DoctorAvailabilityController::class, 'desactivarBloques']);
    Route::post('/doctor/disponibilidad/activar', [DoctorAvailabilityController::class, 'activarBloques']);
    Route::delete('/doctor/disponibilidad/eliminar', [DoctorAvailabilityController::class, 'eliminarBloques']);
    Route::middleware(['auth:api'])->post('/paciente/doctor/disponibilidad', [DoctorAvailabilityController::class, 'verDisponibilidadMedicoPorNombre']);
    Route::get('/medico/citas', [AppointmentController::class, 'listarCitasMedico']);
    Route::post('/medico/citas/cancelar', [AppointmentController::class, 'cancelarCitaMedico']);
  
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

