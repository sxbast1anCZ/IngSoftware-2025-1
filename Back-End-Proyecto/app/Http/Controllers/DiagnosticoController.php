<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Diagnostico;
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
use App\Models\Specialty;


class DiagnosticoController extends Controller
{
    public function registrarDiagnostico(Request $request)
{
    $request->validate([
        'appointment_id'     => 'required|integer',
        'motivo_consulta'    => 'required|string|max:1000',
        'diagnostico'        => 'required|string|max:1000',
        'tratamiento'        => 'required|string|max:1000',
        'notas'              => 'nullable|string|max:2000',
    ]);

    $cita = Appointment::find($request->appointment_id);

    if (!$cita) {
        return response()->json([
            'error' => 'La cita a la cual usted intenta acceder no ha sido efectuada o no existe.'
        ], 404);
    }

    $doctor = JWTAuth::parseToken()->authenticate();

    if ($doctor->id !== $cita->doctor_id || !$doctor->isDoctor()) {
        return response()->json(['error' => 'No está autorizado para registrar diagnóstico en esta cita.'], 403);
    }

    if ($cita->diagnostico) {
        return response()->json(['error' => 'Ya existe un diagnóstico para esta cita.'], 409);
    }

    $diagnostico = Diagnostico::create([
        'appointment_id'   => $cita->id,
        'motivo_consulta'  => $request->motivo_consulta,
        'diagnostico'      => $request->diagnostico,
        'tratamiento'      => $request->tratamiento,
        'notas'            => $request->notas,
    ]);

    return response()->json(['mensaje' => 'Diagnóstico registrado exitosamente.', 'diagnostico' => $diagnostico], 201);
}


    public function verDiagnostico(Request $request)
{
    try {
        $request->validate([
            'appointment_id' => ['required', 'numeric'],
        ], [
            'appointment_id.required' => 'Debe proporcionar el número de la cita.',
            'appointment_id.numeric'  => 'Los parámetros que usted intenta ingresar son incorrectos, por favor, ingrese el número de la cita a la cual quiere acceder a su diagnóstico.',
        ]);

        $appointment = Appointment::find($request->appointment_id);

        if (!$appointment) {
            return response()->json([
                'error' => 'La cita a la cual usted intenta acceder no ha sido efectuada o no existe.'
            ], 404);
        }

        $diagnostico = Diagnostico::where('appointment_id', $request->appointment_id)->first();

        if (!$diagnostico) {
            return response()->json([
                'status' => 'info',
                'message' => 'No se ha registrado ningún diagnóstico para esta cita.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'diagnostico' => $diagnostico
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'error' => 'Error de validación.',
            'detalle' => $e->validator->errors()->first()
        ], 422);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Ocurrió un error al intentar mostrar el diagnóstico.',
            'detalle' => $e->getMessage()
        ], 500);
    }

}

public function historialPaciente(Request $request)
{
    try {
        $paciente = JWTAuth::parseToken()->authenticate();

        if (!$paciente || !$paciente->isPatient()) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        $citas = Appointment::with(['doctor', 'diagnostico', 'diagnostico.licencia'])
            ->where('patient_id', $paciente->id)
            ->whereHas('diagnostico')
            ->orderByDesc('scheduled_at')
            ->get();

        $historial = $citas->map(function ($cita) {
        return [
        'cita_id'         => $cita->id,
        'fecha'           => $cita->scheduled_at,
        'motivo_consulta' => $cita->diagnostico->motivo_consulta,
        'diagnostico'     => $cita->diagnostico->diagnostico,
        'tratamiento'     => $cita->diagnostico->tratamiento,
        'doctor'          => $cita->doctor->name . ' ' . $cita->doctor->lastname,
        'licencia_url'    => optional($cita->diagnostico->licencia)->exists()
            ? url("/api/licencia/pdf/citaLicencia?appointment_id={$cita->id}")
            : null,
        ];
    });


        return response()->json($historial);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Error interno',
            'detalle' => $e->getMessage(),
        ], 500);
    }
}





}
