<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Models\DisponibilidadMedico;
use Illuminate\Database\QueryException;

class AppointmentController extends Controller
{
    

// Método para agendar una cita
        public function scheduleAppointment(Request $request)
    {
    try {
        // Autenticación del usuario
        $user = JWTAuth::parseToken()->authenticate();

        // Validar que sea paciente (role_id = 3)
        if ($user->role_id === 2) {
            return response()->json([
                'error' => 'No se puede reservar una hora desde una cuenta institucional. Por favor, agende su hora con una cuenta personal.'
            ], 403);
        }

        if (!$user->isPatient()) {
            return response()->json([
                'error' => 'Solo los pacientes pueden agendar citas.'
            ], 403);
        }

        // Validaciones del request después de validar el rol
        $validator = Validator::make($request->all(), [
            'doctor_id'     => 'required|numeric',
            'scheduled_at'  => 'required|date_format:Y-m-d H:i',
            'reason'        => 'required|string|max:255',
        ], [
            'doctor_id.required'    => 'Debe ingresar el ID del médico.',
            'doctor_id.numeric'     => 'El ID del médico debe ser numérico.',
            'scheduled_at.required' => 'Debe ingresar la fecha y hora.',
            'scheduled_at.date_format' => 'Por favor, ingrese un formato de hora válido. (Ej: 2025-06-20 09:30)',
            'reason.required'       => 'Debe ingresar una razón para la cita.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'   => 'Error de validación.',
                'detalle' => $validator->errors()->first()
            ], 422);
        }

        // Verificar que el doctor exista y sea médico
        $doctor = User::where('id', $request->doctor_id)->where('role_id', 2)->first();
        if (!$doctor) {
            return response()->json([
                'error' => 'Usted está intentando agendar hora con un médico que no existe.',
            ], 404);
        }

        // Procesar hora y día
        $fechaHora = Carbon::createFromFormat('Y-m-d H:i', $request->scheduled_at);
        $diaSemana = $fechaHora->dayOfWeekIso;
        $hora      = $fechaHora->format('H:i:s');

        // Verificar disponibilidad
        $disponibilidad = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->whereRaw('? BETWEEN hora_inicio AND hora_fin', [$hora])
            ->first();

        if (!$disponibilidad) {
            return response()->json([
                'error' => 'El médico no está disponible en ese horario.',
            ], 400);
        }

        // Verificar conflicto
        $conflicto = Appointment::where('doctor_id', $doctor->id)
            ->where('scheduled_at', $request->scheduled_at)
            ->exists();

        if ($conflicto) {
            return response()->json([
                'error' => 'El horario en el que intentas agendar ya está ocupado.',
            ], 409);
        }

        // Crear la cita
        $cita = Appointment::create([
            'patient_id'     => $user->id,
            'doctor_id'      => $doctor->id,
            'scheduled_at'   => $request->scheduled_at,
            'duration'       => 30,
            'price'          => $disponibilidad->precio,
            'payment_method' => 'pendiente',
            'status'         => 'pendiente',
            'reason'         => $request->reason,
        ]);

        return response()->json([
            'message'     => 'Cita agendada exitosamente.',
            'appointment' => $cita,
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'error'   => 'Ocurrió un error inesperado al agendar la cita.',
            'detalle' => $e->getMessage(),
        ], 500);
    }
    }




//Metodo para obtener la lista de médicos
//Este método devuelve una lista de MÉDICOS solamente, también se implementa una advertencia en el caso de que no haya médicos
//Ruta PÚBLICA, sin autenticación
//Devuelve un JSON con la id del médico, su nombre, apellido, rut y especialidad
    public function listarMedicosPublicos()
    {
        $medicos = User::where('role_id', 3)
        ->where('enabled', true)
        ->whereNotNull('specialty_id')
        ->join('specialties', 'users.specialty_id', '=', 'specialties.id')
        ->select(
            'users.id as user_id',
            'users.name',
            'users.lastname',
            'users.rut',
            'users.specialty_id',
            'specialties.name as specialty_name'
        )
        ->orderBy('users.lastname') //Se ordenan por apellido (orden de despliegue)
        ->get();

    if ($medicos->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'No se ha registrado ningún médico aún.'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $medicos
    ]);
    }








































}
