<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadMedico;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class DoctorAvailabilityController extends Controller
{
    /**
     * Obtener al usuario autenticado con sus relaciones cargadas
     */
    private function getAuthenticatedDoctor()
    {
        $doctor = Auth::guard('api')->user();

    if (!$doctor || !$doctor instanceof \App\Models\User) {
        abort(403, 'No autorizado');
    }

    // Carga relaciones manualmente si todo está bien
    $doctor->load('role', 'disponibilidades');


        if (!$doctor || !$doctor->isDoctor()) {
            abort(403, 'No autorizado');
        }

        return $doctor;
    }

    /**
     * Mostrar disponibilidad actual del médico autenticado
     */
    public function index()
    {
        $doctor = $this->getAuthenticatedDoctor();

        $disponibilidad = $doctor->disponibilidades->sortBy('dia_semana')->values();

        return response()->json(['disponibilidad' => $disponibilidad]);
    }


public function crearDisponibilidadMedico(Request $request)
{
    $doctor = $this->getAuthenticatedDoctor();

    $validator = Validator::make($request->all(), [
        'disponibilidad' => ['required', 'array', 'min:1'],
        'disponibilidad.*.dia_semana' => ['required', 'integer', 'between:1,7'],
        'disponibilidad.*.hora_inicio' => ['required', 'date_format:H:i'],
        'disponibilidad.*.hora_fin' => ['required', 'date_format:H:i', 'after:disponibilidad.*.hora_inicio'],
        'disponibilidad.*.precio' => [
            'required',
            function ($attribute, $value, $fail) {
                $precioStr = (string) $value;

                if (!ctype_digit($precioStr)) {
                    return $fail('El precio debe ser un número entero válido en pesos chilenos dentro del rango admitido (menos de $9.999.999 CLP).');
                }

                if (strlen($precioStr) > 7) {
                    return $fail('El precio no puede superar los $9.999.999 CLP.');
                }
            }
        ],
    ], [
        'disponibilidad.required' => 'Por favor ingrese una disponibilidad del médico.',
        'disponibilidad.array' => 'El formato de disponibilidad debe ser un arreglo.',
        'disponibilidad.min' => 'Debe ingresar al menos una disponibilidad.',

        'disponibilidad.*.dia_semana.required' => 'El campo día de la semana es obligatorio.',
        'disponibilidad.*.dia_semana.integer' => 'El campo día de la semana debe ser un número.',
        'disponibilidad.*.dia_semana.between' => 'Por favor seleccione un día de semana válido. Considere que 1 es Lunes y 7 es Domingo.',

        'disponibilidad.*.hora_inicio.required' => 'La hora de inicio es obligatoria.',
        'disponibilidad.*.hora_inicio.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',

        'disponibilidad.*.hora_fin.required' => 'La hora de fin es obligatoria.',
        'disponibilidad.*.hora_fin.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
        'disponibilidad.*.hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',

        'disponibilidad.*.precio.required' => 'El precio es obligatorio.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Errores de validación.',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Verificar duplicados en el request
    $horariosPropuestos = [];
    foreach ($request->disponibilidad as $index => $bloque) {
        $key = $bloque['dia_semana'] . '-' . $bloque['hora_inicio'] . '-' . $bloque['hora_fin'];
        if (array_key_exists($key, $horariosPropuestos)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Existen horarios duplicados en su solicitud.',
                'duplicado' => [
                    'día' => $bloque['dia_semana'],
                    'inicio' => $bloque['hora_inicio'],
                    'fin' => $bloque['hora_fin']
                ]
            ], 422);
        }
        $horariosPropuestos[$key] = $index;
    }

    // Verificar disponibilidades con la misma hora de inicio
    $horasInicioAgrupadas = [];
    foreach ($request->disponibilidad as $index => $bloque) {
        $key = $bloque['dia_semana'] . '-' . $bloque['hora_inicio'];
        if (isset($horasInicioAgrupadas[$key])) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pueden crear múltiples disponibilidades que comiencen a la misma hora.',
                'conflicto' => [
                    'día' => $bloque['dia_semana'],
                    'hora_inicio' => $bloque['hora_inicio']
                ]
            ], 422);
        }
        $horasInicioAgrupadas[$key] = $index;
    }

    // Procesamos cada bloque de disponibilidad
    $creados = 0;
    $errores = [];

    foreach ($request->disponibilidad as $index => $bloque) {
        $precioStr = (string) $bloque['precio'];
        if (!ctype_digit($precioStr) || strlen($precioStr) > 7) {
            $errores[] = [
                'index' => $index,
                'mensaje' => 'Precio fuera de rango o en formato inválido.'
            ];
            continue;
        }

        // Verificar si ya existe una disponibilidad exactamente igual
        $duplicadoExacto = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->exists();

        if ($duplicadoExacto) {
            $errores[] = [
                'index' => $index,
                'mensaje' => 'Ya existe este horario exacto para el día seleccionado.'
            ];
            continue;
        }

        // Verificar si ya existe una disponibilidad con la misma hora de inicio
        $mismaHoraInicio = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->exists();

        if ($mismaHoraInicio) {
            $errores[] = [
                'index' => $index,
                'mensaje' => 'Ya existe un horario que comienza a la misma hora para el día seleccionado.'
            ];
            continue;
        }

        // Verificar si hay algún solapamiento general
        $solapamiento = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where(function($query) use ($bloque) {
                // Verifica cualquier tipo de solapamiento
                // Caso 1: El nuevo horario comienza durante un horario existente
                $query->where(function($q) use ($bloque) {
                    $q->where('hora_inicio', '<', $bloque['hora_inicio'])
                      ->where('hora_fin', '>', $bloque['hora_inicio']);
                })
                // Caso 2: El nuevo horario termina durante un horario existente
                ->orWhere(function($q) use ($bloque) {
                    $q->where('hora_inicio', '<', $bloque['hora_fin'])
                      ->where('hora_fin', '>', $bloque['hora_fin']);
                })
                // Caso 3: El nuevo horario contiene completamente a un horario existente
                ->orWhere(function($q) use ($bloque) {
                    $q->where('hora_inicio', '>=', $bloque['hora_inicio'])
                      ->where('hora_fin', '<=', $bloque['hora_fin']);
                });
            })
            ->exists();

        if ($solapamiento) {
            $errores[] = [
                'index' => $index,
                'mensaje' => 'El horario se solapa con otro existente para el mismo día.'
            ];
            continue;
        }

        // Crear la disponibilidad
        DisponibilidadMedico::create([
            'user_id' => $doctor->id,
            'dia_semana' => $bloque['dia_semana'],
            'hora_inicio' => $bloque['hora_inicio'],
            'hora_fin' => $bloque['hora_fin'],
            'precio' => (int) $bloque['precio'],
            'activo' => true
        ]);

        $creados++;
    }

    // Respuesta según el resultado
    if (count($errores) > 0) {
        return response()->json([
            'status' => 'warning',
            'message' => $creados > 0 
                ? 'Se registraron ' . $creados . ' disponibilidades, pero hubo errores con algunas.' 
                : 'No se pudo registrar ninguna disponibilidad.',
            'errores' => $errores
        ], $creados > 0 ? 207 : 422);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Disponibilidad registrada correctamente.'
    ], 201);
}


    /**
     * Actualizar disponibilidad del médico
     */
        public function actualizarDisponibilidadMedico(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

    $validator = Validator::make($request->all(), [
        'disponibilidad' => 'required|array|min:1',
        'disponibilidad.*.dia_semana' => 'required|integer|between:1,7',
        'disponibilidad.*.hora_inicio' => ['required', 'date_format:H:i'],
        'disponibilidad.*.hora_fin' => ['required', 'date_format:H:i'],
        'disponibilidad.*.precio' => [
            'required',
            function ($attribute, $value, $fail) {
                $precioStr = (string) $value;

                if (str_contains(strtolower($precioStr), 'e')) {
                    return $fail('El precio ingresado es demasiado grande o inválido.');
                }

                if (!ctype_digit($precioStr)) {
                    return $fail('El precio debe ser un número entero válido en pesos chilenos.');
                }

                if (strlen($precioStr) > 7) {
                    return $fail('El precio no puede superar los $9.999.999 CLP.');
                }
            }
        ],
    ], [
        'disponibilidad.required' => 'Por favor ingrese una disponibilidad del médico para actualizar.',
        'disponibilidad.*.dia_semana.between' => 'Por favor seleccione un día de semana válido. Considere que 1 es Lunes y 7 es Domingo.',
        'disponibilidad.*.hora_inicio.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
        'disponibilidad.*.hora_fin.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
        'disponibilidad.*.hora_fin.after' => 'El intervalo horario que usted ha ingresado es inválido, por favor ingrese una hora de inicio válida.',
        'disponibilidad.*.precio.required' => 'El precio es obligatorio.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Errores de validación.',
            'errors' => $validator->errors(),
        ], 422);
    }

    foreach ($request->disponibilidad as $bloque) {
        // Validación redundante de seguridad (por si el JSON ya vino alterado)
        $precioStr = (string) $bloque['precio'];
        if (
            str_contains(strtolower($precioStr), 'e') ||
            !ctype_digit($precioStr) ||
            strlen($precioStr) > 7
        ) {
            return response()->json([
                'message' => 'El precio ingresado es inválido o supera el límite permitido.',
            ], 422);
        }

        $disponibilidadExistente = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->first();

        if (!$disponibilidadExistente) {
            $dias = [
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
            ];
            return response()->json([
                'message' => 'Error al actualizar, el médico no ha registrado disponibilidades para el día ' . $dias[$bloque['dia_semana']],
            ], 404);
        }

        $disponibilidadExistente->update([
            'hora_inicio' => $bloque['hora_inicio'],
            'hora_fin'    => $bloque['hora_fin'],
            'precio'      => (int) $bloque['precio'], // Guardado como unsignedBigInteger
            'activo'      => true
        ]);
    }

    return response()->json([
        'message' => 'Disponibilidad actualizada correctamente.'
    ]);
    }





    /*
     * Ver citas futuras del médico 
     */
        public function citas()
    {
    $doctor = $this->getAuthenticatedDoctor();

    if (!$doctor) {
        return response()->json([
            'status' => 'error',
            'message' => 'Solo los médicos pueden ver esta información.'
        ], 403);
    }

    $citas = $doctor->citasMedicas()
        ->with('patient')
        ->whereDate('scheduled_at', '>=', now()->toDateString())
        ->orderBy('scheduled_at')
        ->get();

    if ($citas->isEmpty()) {
        return response()->json([
            'status' => 'info',
            'message' => 'Este doctor no cuenta con ninguna cita agendada.'
        ], 200);
    }

    $resultado = $citas->map(function ($cita) {
        return [
            'paciente'        => $cita->patient->name . ' ' . $cita->patient->lastname,
            'dia_semana'      => \Carbon\Carbon::parse($cita->scheduled_at)->isoFormat('dddd'),
            'hora_inicio'     => \Carbon\Carbon::parse($cita->scheduled_at)->format('H:i'),
            'hora_fin'        => \Carbon\Carbon::parse($cita->scheduled_at)->addMinutes($cita->duration)->format('H:i'),
            'razon_consulta'  => $cita->reason,
        ];
    });

    return response()->json([
        'status' => 'success',
        'citas'  => $resultado
    ]);
    }





    //El metodo desactiva los bloques de horarios que se le envien.
        public function desactivarBloques(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

    $validator = Validator::make($request->all(), [
        'bloques' => 'required|array|min:1',
        'bloques.*.dia_semana'   => 'required|integer|between:1,7',
        'bloques.*.hora_inicio'  => 'required|date_format:H:i',
        'bloques.*.hora_fin'     => 'required|date_format:H:i|after:bloques.*.hora_inicio',
    ], [
        'bloques.required' => 'Por favor ingrese un usuario para editar.',
        'bloques.array' => 'El formato de los bloques debe ser un arreglo.',
        'bloques.min' => 'Debe especificar al menos un bloque para desactivar.',

        'bloques.*.dia_semana.required' => 'El día de la semana es obligatorio.',
        'bloques.*.dia_semana.integer'  => 'El día de la semana debe ser numérico.',
        'bloques.*.dia_semana.between'  => 'El día de la semana debe estar entre 1 (Lunes) y 7 (Domingo).',

        'bloques.*.hora_inicio.required' => 'La hora de inicio es obligatoria.',
        'bloques.*.hora_inicio.date_format' => 'Formato inválido en la hora de inicio. Use HH:MM.',

        'bloques.*.hora_fin.required' => 'La hora de fin es obligatoria.',
        'bloques.*.hora_fin.date_format' => 'Formato inválido en la hora de fin. Use HH:MM.',
        'bloques.*.hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Errores de validación.',
            'errors'  => $validator->errors()
        ], 422);
    }

    foreach ($request->bloques as $bloque) {
        // Verificar si el bloque existe exactamente
        $bloqueExistente = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->first();

        if (!$bloqueExistente) {
            $dias = [
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
            ];
            return response()->json([
                'message' => 'Usted está intentando desactivar un bloque inexistente, por favor verifique que el médico tiene disponibilidad ese día y en ese horario.',
                'dia_semana' => $dias[$bloque['dia_semana']] ?? 'Día inválido'
            ], 404);
        }

        // Verificar si hay citas en el horario que se intenta desactivar
        $tieneCitas = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', '>=', now())
            ->whereRaw('DAYOFWEEK(scheduled_at) = ?', [$bloque['dia_semana'] + 1])
            ->whereTime('scheduled_at', '>=', $bloque['hora_inicio'])
            ->whereTime('scheduled_at', '<', $bloque['hora_fin'])
            ->exists();

        if ($tieneCitas) {
            return response()->json([
                'message' => 'No puede desactivar este bloque porque tiene citas programadas. Cancele o reagende primero.',
                'bloque_conflictivo' => $bloque
            ], 422);
        }

        // Desactivar el bloque
        $bloqueExistente->update(['activo' => false]);
    }

        return response()->json(['message' => 'Bloques desactivados correctamente.']);
    }

    public function activarBloques(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

    $validator = Validator::make($request->all(), [
        'bloques' => 'required|array|min:1',
        'bloques.*.dia_semana'   => 'required|integer|between:1,7',
        'bloques.*.hora_inicio'  => 'required|date_format:H:i',
        'bloques.*.hora_fin'     => 'required|date_format:H:i|after:bloques.*.hora_inicio',
    ], [
        'bloques.required' => 'Por favor ingrese un bloque para activar.',
        'bloques.*.dia_semana.between' => 'Por favor seleccione un día de semana válido. Considere que 1 es Lunes y 7 es Domingo.',
        'bloques.*.hora_inicio.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
        'bloques.*.hora_fin.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
        'bloques.*.hora_fin.after' => 'El intervalo horario que usted ha ingresado es inválido, por favor ingrese una hora de inicio válida.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Errores de validación.',
            'errors' => $validator->errors()
        ], 422);
    }

    foreach ($request->bloques as $bloque) {
        // Verificar que el bloque exista
        $bloqueExistente = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->first();

        if (!$bloqueExistente) {
            $dias = [
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
            ];
            return response()->json([
                'message' => 'Usted está intentando activar un bloque inexistente. Por favor verifique que el médico tiene disponibilidad ese día (' . ($dias[$bloque['dia_semana']] ?? 'Desconocido') . ').',
            ], 404);
        }

        $bloqueExistente->update(['activo' => true]);
    }

        return response()->json(['message' => 'Bloques activados correctamente.']);
    }





    


public function verDisponibilidadMedicoPorNombre(Request $request)
{
    $user = Auth::user();

    // Verificar que el solicitante sea un paciente
    if (!$user || $user->role_id !== 3) { 
        return response()->json(['error' => 'Acceso no autorizado.'], 403);
    }

    // Validar el input
    $request->validate([
        'nombre'   => 'required|string',
        'apellido' => 'required|string',
    ]);

    // Buscar al médico exacto por nombre y apellido
    $medico = User::where('role_id', 2) // role_id 3 para médicos
        ->where('name', $request->nombre)
        ->where('lastname', $request->apellido)
        ->first();

    if (!$medico) {
        return response()->json(['error' => 'Médico no encontrado.'], 404);
    }

    // Obtener disponibilidad activa
    $disponibilidad = $medico->disponibilidades()
        ->where('activo', true)
        ->get(['dia_semana', 'hora_inicio', 'hora_fin']);

    return response()->json([
        'nombre'         => $medico->name,
        'apellido'       => $medico->lastname,
        'disponibilidad' => $disponibilidad
    ]);
}

/**
 * Elimina bloques de disponibilidad permanentemente
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function eliminarBloques(Request $request)
{
    $doctor = $this->getAuthenticatedDoctor();

    $validator = Validator::make($request->all(), [
        'bloques' => 'required|array|min:1',
        'bloques.*.dia_semana' => 'required|integer|between:1,7',
        'bloques.*.hora_inicio' => 'required|date_format:H:i',
        'bloques.*.hora_fin' => 'required|date_format:H:i|after:bloques.*.hora_inicio',
    ], [
        'bloques.required' => 'Por favor ingrese al menos un bloque para eliminar.',
        'bloques.array' => 'El formato de los bloques debe ser un arreglo.',
        'bloques.min' => 'Debe especificar al menos un bloque para eliminar.',

        'bloques.*.dia_semana.required' => 'El día de la semana es obligatorio.',
        'bloques.*.dia_semana.integer' => 'El día de la semana debe ser numérico.',
        'bloques.*.dia_semana.between' => 'El día de la semana debe estar entre 1 (Lunes) y 7 (Domingo).',

        'bloques.*.hora_inicio.required' => 'La hora de inicio es obligatoria.',
        'bloques.*.hora_inicio.date_format' => 'Formato inválido en la hora de inicio. Use HH:MM.',

        'bloques.*.hora_fin.required' => 'La hora de fin es obligatoria.',
        'bloques.*.hora_fin.date_format' => 'Formato inválido en la hora de fin. Use HH:MM.',
        'bloques.*.hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Errores de validación.',
            'errors' => $validator->errors()
        ], 422);
    }

    $bloquesProcesados = 0;
    $bloquesFallidos = [];
    $diasSemana = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
    ];

    foreach ($request->bloques as $bloque) {
        // Verificar si el bloque existe
        $bloqueExistente = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->first();

        if (!$bloqueExistente) {
            $bloquesFallidos[] = [
                'bloque' => $bloque,
                'motivo' => 'No existe este bloque de disponibilidad',
                'dia' => $diasSemana[$bloque['dia_semana']] ?? 'Día inválido'
            ];
            continue;
        }

        // Verificar si hay citas programadas en este bloque
        $tieneCitas = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', '>=', now())
            ->whereRaw('DAYOFWEEK(scheduled_at) = ?', [$bloque['dia_semana'] + 1])
            ->whereTime('scheduled_at', '>=', $bloque['hora_inicio'])
            ->whereTime('scheduled_at', '<', $bloque['hora_fin'])
            ->exists();

        if ($tieneCitas) {
            $bloquesFallidos[] = [
                'bloque' => $bloque,
                'motivo' => 'Existen citas programadas para este horario',
                'dia' => $diasSemana[$bloque['dia_semana']] ?? 'Día inválido'
            ];
            continue;
        }

        // Eliminar el bloque
        $bloqueExistente->delete();
        $bloquesProcesados++;
    }

    // Preparar la respuesta
    if (count($bloquesFallidos) > 0 && $bloquesProcesados === 0) {
        // Ningún bloque fue eliminado
        return response()->json([
            'status' => 'error',
            'message' => 'No se pudo eliminar ningún bloque de disponibilidad.',
            'errores' => $bloquesFallidos
        ], 422);
    } elseif (count($bloquesFallidos) > 0) {
        // Algunos bloques fueron eliminados, otros no
        return response()->json([
            'status' => 'warning',
            'message' => 'Se eliminaron ' . $bloquesProcesados . ' bloques, pero hubo errores con ' . count($bloquesFallidos) . ' bloques.',
            'errores' => $bloquesFallidos
        ], 207);
    } else {
        // Todos los bloques fueron eliminados correctamente
        return response()->json([
            'status' => 'success',
            'message' => 'Bloques de disponibilidad eliminados correctamente.',
            'eliminados' => $bloquesProcesados
        ], 200);
    }
}
//Método para obtener por bloques, el nombre del doctor junto al horario que le corresponde en la semana
//Este método devuelve una advertencia si un médico no tiene disponibilidad existente
//Ruta PÚBLICA, sin autenticación pensada para Front-End
//Este método devuelve con JSON con el nombre y apellido del doctor, junto con su horario por bloque
    public function listarDisponibilidadPublica(): JsonResponse
    {
        // Obtener médicos con bloques activos
        $medicos = User::where('role_id', 3)
        ->whereHas('disponibilidades', function ($query) {
            $query->where('activo', true);
        })
        ->with(['disponibilidades' => function ($query) {
            $query->where('activo', true)->orderBy('dia_semana')->orderBy('hora_inicio');
        }])
        ->get();

    if ($medicos->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'No hay médicos con disponibilidad registrada aún.'
        ], 404);
    }

    $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    $respuesta = $medicos->map(function ($medico) use ($dias) {
        return [
            'doctor' => 'Dr ' . $medico->name . ' ' . $medico->lastname,
            'bloques' => $medico->disponibilidades->map(function ($bloque) use ($dias) {
                return $dias[$bloque->dia_semana] . ', ' . substr($bloque->hora_inicio, 0, 5) . ' - ' . substr($bloque->hora_fin, 0, 5);
            })->toArray(),
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $respuesta
    ]);
    }









}
