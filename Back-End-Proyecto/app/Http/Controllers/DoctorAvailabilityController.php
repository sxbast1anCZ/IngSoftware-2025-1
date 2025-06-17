<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadMedico;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

    /**
     * Actualizar disponibilidad del médico
     */
    public function crearDisponibilidadMedico(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

    try {
        $request->validate([
            'disponibilidad' => 'required|array',
            'disponibilidad.*.dia_semana'   => ['required', 'integer', 'between:1,7'],
            'disponibilidad.*.hora_inicio'  => ['required', 'date_format:H:i'],
            'disponibilidad.*.hora_fin'     => ['required', 'date_format:H:i'],
            'disponibilidad.*.precio'       => ['required', 'numeric', 'min:0'],
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();

        foreach ($errors as $field => $messages) {
            if (str_contains($field, 'dia_semana')) {
                return response()->json(['message' => 'Por favor seleccione un día de semana válido. Considere que 1 es Lunes y 7 es Domingo.'], 422);
            }

            if (str_contains($field, 'hora_inicio') || str_contains($field, 'hora_fin')) {
                return response()->json(['message' => 'Por favor ingrese un formato de hora válido (HH:MM)'], 422);
            }
        }

        return response()->json(['message' => 'Error de validación', 'errores' => $errors], 422);
    }

    foreach ($request->disponibilidad as $bloque) {
        if ($bloque['hora_inicio'] >= $bloque['hora_fin']) {
            return response()->json([
                'message' => 'El intervalo horario que usted ha ingresado es inválido, por favor ingrese una hora de inicio válida.',
                'bloque' => $bloque
            ], 422);
        }

        // Validación de solapamiento de horarios existentes para ese día
        $solapado = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('activo', true)
            ->where(function ($query) use ($bloque) {
                $query->where(function ($q) use ($bloque) {
                    $q->where('hora_inicio', '<', $bloque['hora_fin'])
                      ->where('hora_fin', '>', $bloque['hora_inicio']);
                });
            })->exists();

        if ($solapado) {
            return response()->json([
                'message' => 'El horario ingresado se solapa con otro ya registrado. Por favor revise los bloques existentes.',
                'bloque' => $bloque
            ], 422);
        }

        DisponibilidadMedico::create([
            'user_id'     => $doctor->id,
            'dia_semana'  => $bloque['dia_semana'],
            'hora_inicio' => $bloque['hora_inicio'],
            'hora_fin'    => $bloque['hora_fin'],
            'precio'      => $bloque['precio'],
            'activo'      => true
        ]);
    }

        return response()->json(['message' => 'Disponibilidad registrada correctamente.']);
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

        $request->validate([
        'bloques' => 'required|array',
        'bloques.*.dia_semana'   => 'required|integer|between:1,7',
        'bloques.*.hora_inicio'  => 'required|date_format:H:i',
        'bloques.*.hora_fin'     => 'required|date_format:H:i|after:bloques.*.hora_inicio',
    ]);

        foreach ($request->bloques as $bloque) {
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
            ],422);
            }

        // Desactivar el bloque si existe
        DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->update(['activo' => false]);
    }

        return response()->json(['message' => 'Bloques desactivados correctamente.']);
    }


    public function activarBloques(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

    $request->validate([
        'bloques' => 'required|array',
        'bloques.*.dia_semana'   => 'required|integer|between:1,7',
        'bloques.*.hora_inicio'  => 'required|date_format:H:i',
        'bloques.*.hora_fin'     => 'required|date_format:H:i|after:bloques.*.hora_inicio',
    ]);

    foreach ($request->bloques as $bloque) {
        DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->update(['activo' => true]);
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
