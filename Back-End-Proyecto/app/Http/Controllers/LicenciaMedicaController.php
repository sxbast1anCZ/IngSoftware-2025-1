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
use App\Models\Specialty;
use App\Models\Diagnostico;
use App\Models\LicenciaMedica;


class LicenciaMedicaController extends Controller
{
    public function emitirLicencia(Request $request)
{
    $request->validate([
        'diagnostico_id'  => 'required|exists:diagnosticos,id',
        'dias'            => 'required|integer|min:1|max:30',
        'fecha_inicio'    => 'required|date',
        'motivo'          => 'required|string|max:500',
    ]);

    $diagnostico = Diagnostico::findOrFail($request->diagnostico_id);

    if ($diagnostico->licencia) {
        return response()->json(['error' => 'Ya se ha emitido una licencia para este diagnóstico.'], 409);
    }

    $fechaFin = Carbon::parse($request->fecha_inicio)->addDays($request->dias - 1);

    $licencia = LicenciaMedica::create([
        'diagnostico_id' => $diagnostico->id,
        'dias'           => $request->dias,
        'fecha_inicio'   => $request->fecha_inicio,
        'fecha_fin'      => $fechaFin,
        'motivo'         => $request->motivo,
    ]);

    return response()->json(['mensaje' => 'Licencia médica emitida exitosamente.', 'licencia' => $licencia], 201);
}




    public function verDiagnostico(Request $request)
{
    try {
        // Validar que el input sea numérico
        $request->validate([
            'appointment_id' => ['required', 'numeric'],
        ], [
            'appointment_id.required' => 'Debe proporcionar el número de la cita.',
            'appointment_id.numeric'  => 'Los parámetros que usted intenta ingresar son incorrectos, por favor, ingrese el número de la cita a la cual quiere acceder a su diagnóstico.',
        ]);

        // Verificar si la cita existe
        $appointment = Appointment::find($request->appointment_id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usted está intentando solicitar el diagnóstico de una cita que no existe.'
            ], 404);
        }

        // Buscar diagnóstico asociado
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



}
