<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadMedico;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function update(Request $request)
{
    $doctor = $this->getAuthenticatedDoctor();

    $request->validate([
        'disponibilidad' => 'required|array',
        'disponibilidad.*.dia_semana'   => 'required|integer|between:1,7',
        'disponibilidad.*.hora_inicio'  => 'required|date_format:H:i',
        'disponibilidad.*.hora_fin'     => 'required|date_format:H:i|after:disponibilidad.*.hora_inicio',
        'disponibilidad.*.precio'       => 'required|numeric|min:0',
    ]);

    foreach ($request->disponibilidad as $bloque) {
        // Buscar disponibilidad existente
        $disponibilidad = DisponibilidadMedico::where('user_id', $doctor->id)
            ->where('dia_semana', $bloque['dia_semana'])
            ->where('hora_inicio', $bloque['hora_inicio'])
            ->where('hora_fin', $bloque['hora_fin'])
            ->first();

        // Verificar si hay citas en ese bloque (para evitar modificarlo si ya está reservado)
        $tieneCitas = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', '>=', now())
            ->whereRaw('DAYOFWEEK(scheduled_at) = ?', [$bloque['dia_semana'] + 1])
            ->whereTime('scheduled_at', '>=', $bloque['hora_inicio'])
            ->whereTime('scheduled_at', '<', $bloque['hora_fin'])
            ->exists();

        if ($tieneCitas && $disponibilidad) {
            return response()->json([
                'message' => 'No puede modificar horarios con citas ya programadas. Primero cancele o reagende las citas afectadas.',
                'bloque' => $bloque
            ], 422);
        }

        if ($disponibilidad) {
            // Actualiza el bloque existente
            $disponibilidad->update([
                'precio' => $bloque['precio'],
                'activo' => true
            ]);
        } else {
            // Crea nuevo bloque
            DisponibilidadMedico::create([
                'user_id'     => $doctor->id,
                'dia_semana'  => $bloque['dia_semana'],
                'hora_inicio' => $bloque['hora_inicio'],
                'hora_fin'    => $bloque['hora_fin'],
                'precio'      => $bloque['precio'],
                'activo'      => true
            ]);
        }
    }

    return response()->json(['message' => 'Disponibilidad actualizada correctamente.']);
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







}
