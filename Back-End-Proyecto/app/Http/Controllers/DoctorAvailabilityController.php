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
        ]);

        foreach ($request->disponibilidad as $bloque) {
            $tieneCitas = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('scheduled_at', '>=', now())
                ->whereRaw('DAYOFWEEK(scheduled_at) = ?', [$bloque['dia_semana'] + 1])
                ->exists();

            if ($tieneCitas) {
                return response()->json([
                    'message' => 'No puede modificar horarios con citas ya programadas. Primero cancele o reagende las citas afectadas.'
                ], 422);
            }
        }

        // Eliminar y recrear disponibilidad
        $doctor->disponibilidades()->delete();

        foreach ($request->disponibilidad as $bloque) {
            DisponibilidadMedico::create([
                'user_id'     => $doctor->id,
                'dia_semana'  => $bloque['dia_semana'],
                'hora_inicio' => $bloque['hora_inicio'],
                'hora_fin'    => $bloque['hora_fin'],
                'activo'      => true,
            ]);
        }

        return response()->json(['message' => 'Disponibilidad actualizada con éxito.']);
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
}
