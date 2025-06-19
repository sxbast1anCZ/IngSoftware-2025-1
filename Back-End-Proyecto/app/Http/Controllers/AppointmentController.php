<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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


/**
 * Obtener información completa de médicos para reserva de citas
 * 
 * Este método devuelve todos los datos necesarios de los médicos para que el paciente
 * pueda seleccionar un médico y luego reservar una cita según su disponibilidad.
 * 
 * @return \Illuminate\Http\JsonResponse
 */
public function obtenerDatosMedicoReserva()
{
    try {
        // Obtener todos los médicos activos con su especialidad
        $medicos = User::where('role_id', 2) // Rol de médico
            ->where('enabled', true)
            ->whereNotNull('specialty_id')
            ->join('specialties', 'users.specialty_id', '=', 'specialties.id')
            ->select(
                'users.id',
                'users.name',
                'users.lastname',
                'users.rut',
                'users.specialty_id',
                'specialties.name as specialty_name'
            )
            ->orderBy('users.lastname')
            ->get();

        if ($medicos->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se ha registrado ningún médico aún.'
            ], 404);
        }

        // Para cada médico, obtener la duración de consulta y precio
        $resultado = $medicos->map(function ($medico) {
            // Obtener la primera disponibilidad para conocer precio (usamos el precio como referencia)
            $disponibilidad = DisponibilidadMedico::where('user_id', $medico->id)
                ->where('activo', true)
                ->first();

            // Verificar bloques de disponibilidad
            $tieneBloques = DisponibilidadMedico::where('user_id', $medico->id)
                ->where('activo', true)
                ->exists();

            // Preparar días disponibles del médico
            $diasDisponibles = DisponibilidadMedico::where('user_id', $medico->id)
                ->where('activo', true)
                ->select('dia_semana')
                ->distinct()
                ->get()
                ->pluck('dia_semana')
                ->toArray();

            $nombresDias = [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                7 => 'Domingo'
            ];

            $diasDisponiblesNombres = array_map(function($dia) use ($nombresDias) {
                return $nombresDias[$dia] ?? "Desconocido";
            }, $diasDisponibles);

            return [
                'id' => $medico->id,
                'nombre' => $medico->name,
                'apellido' => $medico->lastname,
                'nombre_completo' => $medico->name . ' ' . $medico->lastname,
                'rut' => $medico->rut,
                'especialidad_id' => $medico->specialty_id,
                'especialidad' => $medico->specialty_name,
                'duracion_consulta' => 30, // Duración fija de 30 minutos
                'valor_consulta' => $disponibilidad ? $disponibilidad->precio : null,
                'tiene_disponibilidad' => $tieneBloques,
                'dias_disponibles' => $diasDisponibles,
                'dias_disponibles_nombres' => $diasDisponiblesNombres
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $resultado
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener la información de los médicos',
            'error' => $e->getMessage()
        ], 500);
    }
}
/**
 * Obtener disponibilidad de un médico por fecha
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function obtenerDisponibilidadMedicoPorFecha(Request $request)
{
    $validator = Validator::make($request->all(), [
        'doctor_id' => 'required|numeric',
        'fecha' => 'required|date_format:Y-m-d',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Datos inválidos',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Verificar que el doctor exista
        $doctor = User::where('id', $request->doctor_id)
            ->where('role_id', 2)
            ->where('enabled', true)
            ->first();
            
        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Médico no encontrado o no disponible'
            ], 404);
        }

        // Determinar día de la semana de la fecha solicitada (1=lunes, 7=domingo)
        $fecha = Carbon::createFromFormat('Y-m-d', $request->fecha);
        $diaSemana = $fecha->dayOfWeekIso; // 1 (lunes) a 7 (domingo)

        // Obtener bloques de disponibilidad del médico para ese día
        $bloques = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($bloques->isEmpty()) {
            return response()->json([
                'status' => 'warning',
                'message' => 'El médico no tiene horarios disponibles para la fecha seleccionada',
                'data' => [
                    'doctor' => [
                        'id' => $doctor->id,
                        'nombre' => $doctor->name,
                        'apellido' => $doctor->lastname,
                        'especialidad' => $doctor->specialty->name ?? null
                    ],
                    'fecha' => $request->fecha,
                    'dia_semana' => $diaSemana,
                    'horarios_disponibles' => []
                ]
            ], 200);
        }

        // Obtener citas ya programadas para esa fecha y médico
        $citasOcupadas = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $request->fecha)
            ->where('status', '!=', 'cancelada')
            ->select('scheduled_at')
            ->get()
            ->pluck('scheduled_at')
            ->map(function ($dateTime) {
                return Carbon::parse($dateTime)->format('H:i:s');
            })
            ->toArray();

        // Procesar los bloques y generar slots de 30 minutos
        $horariosDisponibles = [];
        foreach ($bloques as $bloque) {
            $inicio = Carbon::createFromFormat('H:i:s', $bloque->hora_inicio);
            $fin = Carbon::createFromFormat('H:i:s', $bloque->hora_fin);
            
            while ($inicio->lt($fin)) {
                $horaActual = $inicio->format('H:i:s');
                
                // Verificar si el horario está ya ocupado
                if (!in_array($horaActual, $citasOcupadas)) {
                    $horariosDisponibles[] = [
                        'hora' => $horaActual,
                        'hora_formateada' => Carbon::parse($horaActual)->format('H:i'),
                        'precio' => $bloque->precio,
                        'disponible' => true
                    ];
                }
                
                // Avanzar 30 minutos
                $inicio->addMinutes(30);
            }
        }
        
        // Ordenar horarios por hora
        usort($horariosDisponibles, function ($a, $b) {
            return $a['hora'] <=> $b['hora'];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'doctor' => [
                    'id' => $doctor->id,
                    'nombre' => $doctor->name,
                    'apellido' => $doctor->lastname,
                    'especialidad' => $doctor->specialty->name ?? null
                ],
                'fecha' => $request->fecha,
                'dia_semana' => $diaSemana,
                'horarios_disponibles' => $horariosDisponibles
            ]
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener la disponibilidad',
            'error' => $e->getMessage()
        ], 500);
    }
}


/**
 * Listar todos los médicos con su especialidad, valor de consulta y disponibilidad
 * con estados detallados de cada bloque horario
 * 
 * @return \Illuminate\Http\JsonResponse
 */
public function listarMedicosCompleto()
{
    try {
        // Obtener todos los médicos activos con su especialidad
        $medicos = User::where('role_id', 2) // Rol de médico = 2
            ->where('enabled', true)
            ->whereNotNull('specialty_id')
            ->with(['specialty' => function ($query) {
                $query->select('id', 'name');
            }])
            ->select(
                'id',
                'name',
                'lastname',
                'rut',
                'specialty_id',
                'enabled'
            )
            ->orderBy('lastname')
            ->get();

        if ($medicos->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se ha registrado ningún médico aún.'
            ], 404);
        }

        // Procesar cada médico
        $medicosFormateados = $medicos->map(function ($medico) {
            // Obtener TODA la disponibilidad del médico (incluidos bloques inactivos)
            $disponibilidad = DisponibilidadMedico::where('user_id', $medico->id)
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();
            
            // Obtener valor promedio de consulta (solo de bloques activos)
            $valorConsulta = $disponibilidad->where('activo', true)->avg('precio') ?? 0;
            
            // Formatear los días disponibles para mejor legibilidad
            $diasSemana = [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                7 => 'Domingo'
            ];
            
            // Obtener citas programadas para este médico para las próximas 2 semanas
            $fechaHasta = Carbon::now()->addDays(14);
            $citasOcupadas = Appointment::where('doctor_id', $medico->id)
                ->where('scheduled_at', '>=', Carbon::now())
                ->where('scheduled_at', '<=', $fechaHasta)
                ->where('status', '!=', 'cancelada')
                ->get()
                ->groupBy(function($cita) {
                    // Agrupar por día de la semana
                    return Carbon::parse($cita->scheduled_at)->dayOfWeekIso;
                })
                ->map(function($citasDelDia) {
                    // Mapear a horas exactas
                    return $citasDelDia->map(function($cita) {
                        return Carbon::parse($cita->scheduled_at)->format('H:i:s');
                    })->toArray();
                })
                ->toArray();
            
            // Agrupar bloques por día de la semana
            $bloquesPorDia = [];
            
            foreach ($disponibilidad as $bloque) {
                $dia = $bloque->dia_semana;
                $diaNombre = $diasSemana[$dia] ?? "Día $dia";
                
                if (!isset($bloquesPorDia[$diaNombre])) {
                    $bloquesPorDia[$diaNombre] = [];
                }
                
                // Determinar estado del bloque
                $estado = 'eliminado';
                
                if ($bloque->exists) {
                    $estado = $bloque->activo ? 'habilitado' : 'bloqueado';
                }
                
                // Generar slots de 30 minutos para este bloque
                $inicio = Carbon::createFromFormat('H:i:s', $bloque->hora_inicio);
                $fin = Carbon::createFromFormat('H:i:s', $bloque->hora_fin);
                
                while ($inicio->lt($fin)) {
                    $horaActual = $inicio->format('H:i:s');
                    $horaFormateada = substr($horaActual, 0, 5);
                    
                    // Verificar si este horario está ocupado
                    $ocupado = false;
                    if (isset($citasOcupadas[$dia]) && in_array($horaActual, $citasOcupadas[$dia])) {
                        $ocupado = true;
                    }
                    
                    // Estado final del slot
                    $estadoSlot = $estado;
                    if ($estado === 'habilitado' && $ocupado) {
                        $estadoSlot = 'ocupado';
                    }
                    
                    $bloquesPorDia[$diaNombre][] = [
                        'inicio' => $horaFormateada,
                        'fin' => substr($inicio->copy()->addMinutes(30)->format('H:i:s'), 0, 5),
                        'precio' => $bloque->precio,
                        'estado' => $estadoSlot // 'habilitado', 'bloqueado', 'eliminado' o 'ocupado'
                    ];
                    
                    // Avanzar 30 minutos
                    $inicio->addMinutes(30);
                }
            }
            
            // Ordenar bloques dentro de cada día por hora de inicio
            foreach ($bloquesPorDia as $dia => $bloques) {
                usort($bloquesPorDia[$dia], function($a, $b) {
                    return $a['inicio'] <=> $b['inicio'];
                });
            }
            
            // Determinar días específicos en que trabaja (solo activos)
            $diasDisponibles = $disponibilidad->where('activo', true)->pluck('dia_semana')->unique()->values();
            $diasDisponiblesNombres = $diasDisponibles->map(function($dia) use ($diasSemana) {
                return $diasSemana[$dia] ?? "Día $dia";
            })->values()->toArray();
            
            // Estadísticas sobre bloques de horario
            $numBloques = $disponibilidad->count();
            $numBloquesActivos = $disponibilidad->where('activo', true)->count();
            $numBloquesPorEstado = [
                'habilitados' => $numBloquesActivos,
                'bloqueados' => $disponibilidad->where('activo', false)->count(),
                'total' => $numBloques
            ];

            return [
                'id' => $medico->id,
                'nombre' => $medico->name,
                'apellido' => $medico->lastname,
                'nombre_completo' => $medico->name . ' ' . $medico->lastname,
                'rut' => $medico->rut,
                'especialidad' => [
                    'id' => $medico->specialty->id,
                    'nombre' => $medico->specialty->name
                ],
                'valor_consulta' => round($valorConsulta),
                'tiene_disponibilidad' => $numBloquesActivos > 0,
                'dias_disponibles' => $diasDisponiblesNombres,
                'estadisticas_bloques' => $numBloquesPorEstado,
                'horarios' => $bloquesPorDia
            ];
        });

        // Agrupar médicos por especialidad para facilitar el filtrado en el frontend
        $especialidades = $medicosFormateados->groupBy('especialidad.nombre')
            ->map(function($medicos, $nombreEspecialidad) {
                return [
                    'nombre' => $nombreEspecialidad,
                    'medicos' => $medicos->values()
                ];
            })->values();

        return response()->json([
            'status' => 'success',
            'medicos' => $medicosFormateados,
            'especialidades' => $especialidades
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener la información de los médicos',
            'error' => $e->getMessage()
        ], 500);
    }
}


/**
 * Agendar una cita médica por parte de un paciente
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function agendarCita(Request $request)
{
    // Verificar que el usuario está autenticado y es un paciente
    $paciente = Auth::user();
    if (!$paciente || $paciente->role_id != 3) { // 3 es el rol de paciente
        return response()->json([
            'status' => 'error',
            'message' => 'No autorizado. Debe iniciar sesión como paciente.'
        ], 403);
    }

    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'doctor_id' => 'required|numeric|exists:users,id',
        'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
        'hora_inicio' => 'required|date_format:H:i',
        'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        'motivo' => 'required|string|min:5|max:500',
    ], [
        'doctor_id.required' => 'Debe seleccionar un médico.',
        'doctor_id.exists' => 'El médico seleccionado no existe.',
        'fecha.required' => 'Debe seleccionar una fecha para la cita.',
        'fecha.date_format' => 'El formato de fecha es incorrecto. Use YYYY-MM-DD.',
        'fecha.after_or_equal' => 'La fecha debe ser hoy o posterior.',
        'hora_inicio.required' => 'Debe seleccionar una hora de inicio para la cita.',
        'hora_inicio.date_format' => 'El formato de hora de inicio es incorrecto. Use HH:MM.',
        'hora_fin.required' => 'Debe seleccionar una hora de fin para la cita.',
        'hora_fin.date_format' => 'El formato de hora de fin es incorrecto. Use HH:MM.',
        'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        'motivo.required' => 'Debe indicar el motivo de la consulta.',
        'motivo.min' => 'El motivo de la consulta es demasiado corto.',
        'motivo.max' => 'El motivo de la consulta no puede exceder los 500 caracteres.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Errores de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Verificar que el médico exista y esté habilitado
        $doctor = User::where('id', $request->doctor_id)
            ->where('role_id', 2) // Rol de médico
            ->where('enabled', true)
            ->first();

        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'El médico seleccionado no existe o no está disponible.'
            ], 404);
        }

        // Convertir fecha y hora a datetime
        $fechaHoraInicio = Carbon::createFromFormat('Y-m-d H:i', $request->fecha . ' ' . $request->hora_inicio);
        $fechaHoraFin = Carbon::createFromFormat('Y-m-d H:i', $request->fecha . ' ' . $request->hora_fin);
        
        // Verificar que la fecha/hora no esté en el pasado
        if ($fechaHoraInicio->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No puede agendar una cita en el pasado.'
            ], 422);
        }

        // Calcular duración en minutos
        $duracion = $fechaHoraInicio->diffInMinutes($fechaHoraFin);
        
        if ($duracion < 15 || $duracion > 120) {
            return response()->json([
                'status' => 'error',
                'message' => 'La duración de la cita debe estar entre 15 y 120 minutos.'
            ], 422);
        }

        // Obtener el día de la semana de la fecha seleccionada (1=lunes, 7=domingo)
        $diaSemana = $fechaHoraInicio->dayOfWeekIso;
        $horaInicioSeleccionada = $fechaHoraInicio->format('H:i:s');
        $horaFinSeleccionada = $fechaHoraFin->format('H:i:s');

        // Verificar si el médico tiene disponibilidad en ese día y hora
        $disponibilidad = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('hora_inicio', '<=', $horaInicioSeleccionada)
            ->where('hora_fin', '>=', $horaFinSeleccionada)
            ->first();

        if (!$disponibilidad) {
            return response()->json([
                'status' => 'error',
                'message' => 'El médico no tiene horario disponible en el día y hora seleccionados.'
            ], 422);
        }

        // Verificar que no exista una cita ya agendada para ese médico en ese rango de horas
        $citaExistente = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $fechaHoraInicio->toDateString())
            ->where(function($query) use ($horaInicioSeleccionada, $horaFinSeleccionada) {
                // La cita existente comienza dentro del rango solicitado
                $query->whereBetween('scheduled_at', [$horaInicioSeleccionada, $horaFinSeleccionada])
                // O termina dentro del rango solicitado
                ->orWhere(function($q) use ($horaInicioSeleccionada, $horaFinSeleccionada) {
                    $q->whereRaw("ADDTIME(scheduled_at, SEC_TO_TIME(duration * 60)) > ?", [$horaInicioSeleccionada])
                      ->whereRaw("ADDTIME(scheduled_at, SEC_TO_TIME(duration * 60)) <= ?", [$horaFinSeleccionada]);
                })
                // O abarca completamente el rango solicitado
                ->orWhere(function($q) use ($horaInicioSeleccionada, $horaFinSeleccionada) {
                    $q->where('scheduled_at', '<=', $horaInicioSeleccionada)
                      ->whereRaw("ADDTIME(scheduled_at, SEC_TO_TIME(duration * 60)) >= ?", [$horaFinSeleccionada]);
                });
            })
            ->where('status', '!=', 'cancelada')
            ->first();

        if ($citaExistente) {
            return response()->json([
                'status' => 'error',
                'message' => 'El horario seleccionado ya está ocupado. Por favor elija otro.'
            ], 409);
        }

        // Crear la nueva cita
        $cita = Appointment::create([
            'patient_id' => $paciente->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $fechaHoraInicio,
            'duration' => $duracion,
            'reason' => $request->motivo,
            'price' => $disponibilidad->precio,
            'status' => 'agendada',
            'observations' => null
        ]);

        // Enviar correo de confirmación al paciente (opcional)
        try {
            // Importar con namespace completo para evitar el error
            \Illuminate\Support\Facades\Mail::send('emails.cita_confirmada', [
                'paciente' => $paciente,
                'doctor' => $doctor,
                'cita' => $cita,
                'fechaHora' => $fechaHoraInicio->format('d/m/Y H:i'),
                'duracion' => $duracion
            ], function ($message) use ($paciente) {
                $message->to($paciente->email);
                $message->subject('Confirmación de cita médica');
            });
        } catch (\Exception $e) {
            // Log del error al enviar correo usando namespace completo
            \Illuminate\Support\Facades\Log::error('Error al enviar correo de confirmación: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cita agendada correctamente',
            'data' => [
                'cita_id' => $cita->id,
                'doctor' => $doctor->name . ' ' . $doctor->lastname,
                'especialidad' => $doctor->specialty->name ?? null,
                'fecha' => $fechaHoraInicio->format('d/m/Y'),
                'hora_inicio' => $fechaHoraInicio->format('H:i'),
                'hora_fin' => $fechaHoraFin->format('H:i'),
                'duracion' => $duracion . ' minutos',
                'precio' => $disponibilidad->precio,
                'estado' => 'agendada'
            ]
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al agendar la cita',
            'error' => $e->getMessage()
        ], 500);
    }
}



/**
 * Listar citas de un médico con opción de filtrado
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function listarCitasMedico(Request $request)
{
    // Verificar que el usuario está autenticado y es un médico
    $doctor = Auth::user();
    if (!$doctor || $doctor->role_id != 2) { // Asumiendo que 2 es el rol de médico
        return response()->json([
            'status' => 'error',
            'message' => 'No autorizado. Debe iniciar sesión como médico.'
        ], 403);
    }

    // Validar parámetros opcionales de filtrado
    $validator = Validator::make($request->all(), [
        'fecha_desde' => 'nullable|date_format:Y-m-d',
        'fecha_hasta' => 'nullable|date_format:Y-m-d|after_or_equal:fecha_desde',
        'estado' => 'nullable|in:agendada,completada,cancelada,todas',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Parámetros de filtrado inválidos',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Construir la consulta base
        $query = Appointment::where('doctor_id', $doctor->id);
        
        // Aplicar filtros si se proporcionaron
        if ($request->has('fecha_desde')) {
            $query->whereDate('scheduled_at', '>=', $request->fecha_desde);
        }
        
        if ($request->has('fecha_hasta')) {
            $query->whereDate('scheduled_at', '<=', $request->fecha_hasta);
        }
        
        if ($request->has('estado') && $request->estado !== 'todas') {
            $query->where('status', $request->estado);
        }
        
        // Obtener las citas con datos de pacientes
        $citas = $query->with(['patient' => function($query) {
                $query->select('id', 'name', 'lastname', 'rut', 'email', 'phone');
            }])
            ->orderBy('scheduled_at', 'asc')
            ->get();
        
        if ($citas->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No hay citas que coincidan con los filtros',
                'data' => []
            ], 200);
        }
        
        // Formatear las citas para devolver solo la información necesaria
        $citasFormateadas = $citas->map(function($cita) {
            $fechaHora = Carbon::parse($cita->scheduled_at);
            
            return [
                'id' => $cita->id,
                'paciente' => [
                    'id' => $cita->patient->id,
                    'nombre' => $cita->patient->name,
                    'apellido' => $cita->patient->lastname,
                    'nombre_completo' => $cita->patient->name . ' ' . $cita->patient->lastname,
                    'rut' => $cita->patient->rut,
                    'email' => $cita->patient->email,
                    'telefono' => $cita->patient->phone
                ],
                'fecha' => $fechaHora->format('Y-m-d'),
                'hora' => $fechaHora->format('H:i'),
                'fecha_formateada' => $fechaHora->format('d/m/Y'),
                'hora_formateada' => $fechaHora->format('H:i'),
                'motivo' => $cita->reason,
                'estado' => $cita->status,
                'precio' => $cita->price,
                'observaciones' => $cita->observations,
                'puede_cancelar' => $cita->status === 'agendada' && $fechaHora->isFuture(),
                'puede_completar' => $cita->status === 'agendada' && !$fechaHora->isFuture()
            ];
        });
        
        // Agrupar citas por fecha para facilitar la visualización
        $citasPorFecha = $citasFormateadas->groupBy('fecha')
            ->map(function($citasDelDia, $fecha) {
                $fechaCarbon = Carbon::parse($fecha);
                return [
                    'fecha' => $fecha,
                    'fecha_formateada' => $fechaCarbon->format('d/m/Y'),
                    'dia_semana' => $fechaCarbon->locale('es')->dayName,
                    'citas' => $citasDelDia->values()
                ];
            })->values();
            
        // Obtener estadísticas
        $estadisticas = [
            'total' => $citas->count(),
            'agendadas' => $citas->where('status', 'agendada')->count(),
            'completadas' => $citas->where('status', 'completada')->count(),
            'canceladas' => $citas->where('status', 'cancelada')->count()
        ];

        return response()->json([
            'status' => 'success',
            'estadisticas' => $estadisticas,
            'data' => $citasPorFecha
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener las citas',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Cancelar una cita por parte del médico
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function cancelarCitaMedico(Request $request)
{
    // Verificar que el usuario está autenticado y es un médico
    $doctor = Auth::user();
    if (!$doctor || $doctor->role_id != 2) {
        return response()->json([
            'status' => 'error',
            'message' => 'No autorizado. Debe iniciar sesión como médico.'
        ], 403);
    }

    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'cita_id' => 'required|numeric|exists:appointments,id',
        'motivo_cancelacion' => 'required|string|min:5|max:500',
    ], [
        'cita_id.required' => 'Debe proporcionar el ID de la cita.',
        'cita_id.exists' => 'La cita seleccionada no existe.',
        'motivo_cancelacion.required' => 'Debe indicar el motivo de la cancelación.',
        'motivo_cancelacion.min' => 'El motivo de cancelación es demasiado corto.',
        'motivo_cancelacion.max' => 'El motivo de cancelación no puede exceder los 500 caracteres.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Errores de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Buscar la cita asegurándose que pertenece a este médico
        $cita = Appointment::where('id', $request->cita_id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$cita) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cita no existe o no pertenece a este médico.'
            ], 404);
        }

        // Verificar que la cita esté en estado agendada
        if ($cita->status !== 'agendada') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo se pueden cancelar citas en estado "agendada".'
            ], 422);
        }

        // Verificar que la cita sea futura
        $fechaCita = Carbon::parse($cita->scheduled_at);
        if ($fechaCita->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pueden cancelar citas pasadas.'
            ], 422);
        }

        // Actualizar el estado de la cita
        $cita->status = 'cancelada';
        $cita->observations = 'Cancelada por el médico: ' . $request->motivo_cancelacion;
        $cita->save();

        // Notificar al paciente por email (opcional)
        try {
            $paciente = User::find($cita->patient_id);
            Mail::send('emails.cita_cancelada', [
                'paciente' => $paciente,
                'doctor' => $doctor,
                'cita' => $cita,
                'fechaHora' => $fechaCita->format('d/m/Y H:i'),
                'motivo' => $request->motivo_cancelacion
            ], function ($message) use ($paciente) {
                $message->to($paciente->email);
                $message->subject('Cancelación de cita médica');
            });
        } catch (\Exception $e) {
            // Log del error al enviar correo, pero no afecta la transacción
            \Illuminate\Support\Facades\Log::error('Error al enviar correo de cancelación: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cita cancelada correctamente.',
            'data' => [
                'cita_id' => $cita->id,
                'paciente' => User::find($cita->patient_id)->name . ' ' . User::find($cita->patient_id)->lastname,
                'fecha' => $fechaCita->format('d/m/Y'),
                'hora' => $fechaCita->format('H:i'),
                'estado' => 'cancelada'
            ]
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al cancelar la cita',
            'error' => $e->getMessage()
        ], 500);
    }
}










}
