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
        'appointment_id'     => 'required|exists:appointments,id',
        'motivo_consulta'    => 'required|string|max:1000',
        'diagnostico'        => 'required|string|max:1000',
        'tratamiento'        => 'required|string|max:1000',
        'notas'              => 'nullable|string|max:2000',
    ]);

    $doctor = JWTAuth::parseToken()->authenticate();
    $cita = Appointment::findOrFail($request->appointment_id);

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
