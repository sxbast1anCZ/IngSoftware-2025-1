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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;



class LicenciaMedicaController extends Controller
{
    public function emitirLicencia(Request $request)
{
    // Validaciones iniciales
    $request->validate([
        'diagnostico_id'  => 'required|exists:diagnosticos,id',
        'dias'            => 'required|integer|min:1|max:30',
        'fecha_inicio'    => 'required|date',
        'motivo'          => 'required|string|max:500',
    ], [
        'diagnostico_id.required' => 'Debe especificar el diagnóstico asociado.',
        'diagnostico_id.exists'   => 'El diagnóstico indicado no existe.',

        'dias.required' => 'Debe indicar la cantidad de días.',
        'dias.integer'  => 'La cantidad de días debe ser un número entero.',
        'dias.min'      => 'La cantidad de días debe ser mayor a 0.',
        'dias.max'      => 'No puede emitir una licencia médica por más de 30 días.',

        'fecha_inicio.required' => 'Debe indicar la fecha de inicio.',
        'fecha_inicio.date'     => 'La fecha de inicio no tiene un formato válido.',

        'motivo.required' => 'El motivo de la licencia médica es obligatorio.',
        'motivo.string'   => 'El motivo debe ser una cadena de texto.',
        'motivo.max'      => 'El motivo no puede superar los 500 caracteres.',
    ]);

    // Buscar diagnóstico
    $diagnostico = Diagnostico::findOrFail($request->diagnostico_id);

    // Validar que no exista una licencia ya emitida
    if ($diagnostico->licencia) {
        return response()->json([
            'error' => 'Ya se ha emitido una licencia para este diagnóstico.'
        ], 409);
    }

    // Calcular fecha de término
    $fechaFin = Carbon::parse($request->fecha_inicio)->addDays($request->dias - 1);

    // Validar que la fecha de término sea posterior a la de inicio
    if ($fechaFin <= Carbon::parse($request->fecha_inicio)) {
        return response()->json([
            'error' => 'La fecha de término de la licencia debe ser posterior a la fecha de inicio.'
        ], 422);
    }

    // Crear licencia médica
    $licencia = LicenciaMedica::create([
        'diagnostico_id' => $diagnostico->id,
        'dias'           => $request->dias,
        'fecha_inicio'   => $request->fecha_inicio,
        'fecha_fin'      => $fechaFin,
        'motivo'         => $request->motivo,
    ]);

    return response()->json([
        'mensaje' => 'Licencia médica emitida exitosamente.',
        'licencia' => $licencia
    ], 201);
}


    public function verDiagnostico(Request $request) //ver licencia POR diagnóstico
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
                'message' => 'No se ha registrado ningún diagnóstico para esta cita, por lo que no puede haber una licencia.'
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




public function mostrarLicenciaPorCita(Request $request)
{
    try {
        $request->validate([
            'appointment_id' => ['required', 'numeric'],
        ], [
            'appointment_id.required' => 'Debe proporcionar el número de la cita.',
            'appointment_id.numeric'  => 'El ID de la cita debe ser un número válido.',
        ]);

        $cita = Appointment::find($request->appointment_id);

        if (!$cita) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cita a la cual usted intenta acceder no ha sido efectuada o no existe.'
            ], 404);
        }

        $diagnostico = Diagnostico::where('appointment_id', $request->appointment_id)->first();

        if (!$diagnostico) {
            return response()->json([
                'status' => 'info',
                'message' => 'No se ha registrado ningún diagnóstico para esta cita, por lo que no puede haber una licencia.'
            ], 404);
        }

        $licencia = LicenciaMedica::where('diagnostico_id', $diagnostico->id)->first();

        if (!$licencia) {
            return response()->json([
                'status' => 'info',
                'message' => 'No se ha emitido ninguna licencia médica para el diagnóstico de esta cita.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'licencia' => $licencia
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'error' => 'Error de validación.',
            'detalle' => $e->validator->errors()->first()
        ], 422);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Ocurrió un error al intentar mostrar la licencia.',
            'detalle' => $e->getMessage()
        ], 500);
    }
}

public function descargarLicenciaPorCita(Request $request)
{
    // 1. Validación de input
    $validator = Validator::make($request->all(), [
        'appointment_id' => 'required|numeric|exists:appointments,id',
    ], [
        'appointment_id.required' => 'Debe indicar el ID de la cita.',
        'appointment_id.numeric'  => 'El valor ingresado debe ser numérico.',
        'appointment_id.exists'   => 'La cita indicada no existe.',
    ]);

    if ($validator->fails()) {
        // Retornamos siempre el primer error de appointment_id
        return response()->json([
            'error' => $validator->errors()->first('appointment_id')
        ], 422);
    }

    // 2. Autenticación
    $user = JWTAuth::parseToken()->authenticate();

    // 3. Carga de la cita con relaciones
    $cita = Appointment::with(['doctor.specialty', 'patient'])
                      ->find($request->appointment_id);

    // (Aunque el exists ya garantiza que la cita existe, verificamos por seguridad)
    if (! $cita) {
        return response()->json(['error' => 'Cita no encontrada.'], 404);
    }

    // 4. Autorización: solo el paciente que reservó puede descargar
    if ($user->id !== $cita->patient->id) {
        return response()->json(['error' => 'No autorizado.'], 403);
    }

    // 5. Validaciones sobre el diagnóstico y la licencia
    $diagnostico = Diagnostico::where('appointment_id', $cita->id)->first();
    if (! $diagnostico) {
        return response()->json(['error' => 'No hay diagnóstico para esta cita.'], 404);
    }

    $licencia = LicenciaMedica::where('diagnostico_id', $diagnostico->id)->first();
    if (! $licencia) {
        return response()->json(['error' => 'No se ha emitido ninguna licencia médica para esta cita.'], 404);
    }

    // 6. Generación y almacenamiento del PDF con manejo de errores
    try {
        $pdf = PDF::loadView('licencia_pdf', compact('licencia', 'cita'));
        $filename = "licencia_{$licencia->id}.pdf";
        Storage::disk('public')->put("licencias/{$filename}", $pdf->output());
    } catch (\Throwable $e) {
        Log::error("Error generando licencia PDF (ID cita: {$cita->id}): " . $e->getMessage());
        return response()->json([
            'error' => 'Ocurrió un error al generar la licencia médica.'
        ], 500);
    }

    // 7. Respuesta exitosa
    return response()->json([
        'message' => 'Licencia generada correctamente.',
        'url'     => asset("storage/licencias/{$filename}")
    ], 200);
}




}
