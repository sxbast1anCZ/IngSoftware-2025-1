<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\LicenciaMedicaController;



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
Route::middleware('auth:api')->get('/appointments', [AuthController::class, 'getAppointments']);

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

//Rutas para gestionar el diagnóstioc de los médicos
Route::middleware('auth:api')->group(function () {
    Route::post('/diagnostico/registrar', [DiagnosticoController::class, 'registrarDiagnostico']);
    Route::post('/diagnostico/ver', [DiagnosticoController::class, 'verDiagnostico']);

    //Y estas para gestionar cosas de licencias médicas
    Route::post('/licencia/emitir', [LicenciaMedicaController::class, 'emitirLicencia']);
    Route::post('/licencia/ver', [LicenciaMedicaController::class, 'mostrarLicencia']);

});







