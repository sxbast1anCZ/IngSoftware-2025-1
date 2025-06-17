<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadMedico;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


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


// Crea la disponibilidad del médico
    public function crearDisponibilidadMedico(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

    $validator = Validator::make($request->all(), [
        'disponibilidad' => ['required', 'array', 'min:1'],
        'disponibilidad.*.dia_semana' => ['required', 'integer', 'between:1,7'],
        'disponibilidad.*.hora_inicio' => ['required', 'date_format:H:i'],
        'disponibilidad.*.hora_fin' => ['required', 'date_format:H:i'],
        'disponibilidad.*.precio' => ['required', 'numeric', 'min:0', 'max:99999999999999999.99'],
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

        'disponibilidad.*.precio.required' => 'El precio es obligatorio.',
        'disponibilidad.*.precio.numeric' => 'El precio debe ser numérico.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Errores de validación.',
            'errors' => $validator->errors(),
        ], 422);
    }

    foreach ($request->disponibilidad as $bloque) {
        $existe = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Usted está creando un horario para un día que ya cuenta con una disponibilidad, por favor actualice su disponibilidad.',
            ], 409);
        }

        DisponibilidadMedico::create([
            'user_id' => $doctor->id,
            'dia_semana' => $bloque['dia_semana'],
            'hora_inicio' => $bloque['hora_inicio'],
            'hora_fin' => $bloque['hora_fin'],
            'precio' => $bloque['precio'],
            'activo' => true
        ]);
    }

    return response()->json(['message' => 'Disponibilidad registrada correctamente.']);
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
            'numeric',
            'min:0',
            'max:99999999999999999.99',
            'max_digits:20'
    ],
], [
    'disponibilidad.required' => 'Por favor ingrese una disponibilidad del médico para actualizar.',
    'disponibilidad.*.dia_semana.between' => 'Por favor seleccione un día de semana válido. Considere que 1 es Lunes y 7 es Domingo.',
    'disponibilidad.*.hora_inicio.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
    'disponibilidad.*.hora_fin.date_format' => 'Por favor ingrese un formato de hora válido (HH:MM).',
    'disponibilidad.*.hora_fin.after' => 'El intervalo horario que usted ha ingresado es inválido, por favor ingrese una hora de inicio válida.',
    'disponibilidad.*.precio.numeric' => 'El precio debe ser un número válido.',
    'disponibilidad.*.precio.max_digits' => 'El precio no puede superar los 20 caracteres.',
]);


    if ($validator->fails()) {
        return response()->json([
            'message' => 'Errores de validación.',
            'errors' => $validator->errors(),
        ], 422);
    }

    foreach ($request->disponibilidad as $bloque) {
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

        // Actualizar directamente el bloque del día
        $disponibilidadExistente->update([
            'hora_inicio' => $bloque['hora_inicio'],
            'hora_fin'    => $bloque['hora_fin'],
            'precio'      => $bloque['precio'],
            'activo'      => true
        ]);
    }

    return response()->json([
        'message' => 'Disponibilidad actualizada correctamente.'
    ]);
}



    /**
     * Ver citas futuras del médico
     */
    public function citas()
    {
        $doctor = $this->getAuthenticatedDoctor();

        $citas = $doctor->citasMedicas()
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        return response()->json(['citas' => $citas]);
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
    if (!$user || $user->role_id !== 2) { 
        return response()->json(['error' => 'Acceso no autorizado.'], 403);
    }

    // Validar el input
    $request->validate([
        'nombre'   => 'required|string',
        'apellido' => 'required|string',
    ]);

    // Buscar al médico exacto por nombre y apellido
    $medico = User::where('role_id', 3) // role_id 3 para médicos
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








}
