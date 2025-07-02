<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Models\DisponibilidadMedico;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;


class AdminAppointmentController extends Controller
{
    /**
     * Listar todas las citas (igual que antes, usa query params).
     */
    public function index(Request $request)
    {
        $v = Validator::make($request->all(), [
            'status'    => 'nullable|in:pendiente,confirmada,cancelada,all',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);
        if ($v->fails()) {
            return response()->json(['error'=>$v->errors()->first()], 422);
        }

        $citas = Appointment::query()
            ->byStatus($request->status)
            ->when($request->date_from, fn($q)=> $q->whereDate('scheduled_at','>=',$request->date_from))
            ->when($request->date_to,   fn($q)=> $q->whereDate('scheduled_at','<=',$request->date_to))
            ->with(['patient','doctor'])
            ->orderBy('scheduled_at','asc')
            ->paginate(15);

        return response()->json($citas);
    }

    /**
     * Actualizar una cita pasando el ID en el body.
     */
    public function modificarCita(Request $request)
    {
        // 1) Validación básica de entrada
        $v = Validator::make($request->all(), [
            'appointment_id' => ['required','numeric','exists:appointments,id'],
            'scheduled_at'   => ['sometimes','required','date_format:Y-m-d H:i','after_or_equal:now'],
            'doctor_id'      => [
                'sometimes','required','numeric',
                Rule::exists('users','id')->where('role_id', 2)
            ],
            'reason'         => ['sometimes','required','string','max:255'],
        ], [
            'appointment_id.required' => 'Debe indicar el ID de la cita.',
            'appointment_id.numeric'  => 'El ID de la cita debe ser numérico.',
            'appointment_id.exists'   => 'La cita indicada no existe.',
            'doctor_id.exists'        => 'El médico seleccionado no existe o no es un médico.',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()->first()], 422);
        }

        // 2) Cargar la cita
        $appointment = Appointment::findOrFail($request->appointment_id);

        // 3) No permitir cambios si está cancelada o ya ocurrió
        if (
            $appointment->status === Appointment::STATUS_CANCELADA ||
            $appointment->scheduled_at->isPast()
        ) {
            return response()->json(['error' => 'No se puede modificar esta cita.'], 422);
        }

        // 4) Determinar valores definitivos
        $newDoctorId   = $request->has('doctor_id')   ? $request->doctor_id   : $appointment->doctor_id;
        $newScheduled  = $request->has('scheduled_at')
                           ? Carbon::createFromFormat('Y-m-d H:i', $request->scheduled_at)
                           : $appointment->scheduled_at;
                           
          // 5) Comprobar que el médico tenga un bloque activo en ese día/hora
        $diaSemana = $newScheduled->dayOfWeekIso;           // 1 = lunes … 7 = domingo
        $hora      = $newScheduled->format('H:i:s');

        $tieneDisp = DisponibilidadMedico::where('user_id', $newDoctorId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->whereRaw('? BETWEEN hora_inicio AND hora_fin', [$hora])
            ->exists();

        if (! $tieneDisp) {
            return response()->json([
                'error' => 'El médico al que se le intenta asignar la cita no tiene disponibilidad para ese horario.'
            ], 422);
        }

        // 6) Guardar cambios
        $appointment->fill($request->only(['scheduled_at','doctor_id','reason']));
        $appointment->save();

        return response()->json([
            'message'     => 'Cita actualizada correctamente.',
            'appointment' => $appointment->fresh(),
        ]);
    }

    /**
     * Cancelar una cita pasando el ID en el body.
     */
    public function cancelarCita(Request $request)
    {
        $v = Validator::make($request->all(), [
            'appointment_id' => 'required|numeric|exists:appointments,id',
        ], [
            'appointment_id.required' => 'Debe indicar el ID de la cita.',
            'appointment_id.numeric'  => 'El ID de la cita debe ser numérico.',
            'appointment_id.exists'   => 'La cita indicada no existe.',
        ]);
        if ($v->fails()) {
            return response()->json(['error'=>$v->errors()->first()], 422);
        }

        $appointment = Appointment::findOrFail($request->appointment_id);

        if ($appointment->status === Appointment::STATUS_CANCELADA) {
            return response()->json(['error'=>'La cita ya está cancelada.'], 422);
        }

        $appointment->status = Appointment::STATUS_CANCELADA;
        $appointment->save();

        // Notificar al paciente
        try {
            $paciente = $appointment->patient;
            Mail::send('emails.admin_cita_cancelada', [
                'paciente'  => $paciente,
                'cita'      => $appointment,
                'fechaHora' => $appointment->scheduled_at->format('d/m/Y H:i'),
            ], function($msg) use ($paciente) {
                $msg->to($paciente->email)
                    ->subject('Su cita ha sido cancelada por el administrador');
            });
        } catch (\Throwable $e) {
            Log::error("Error enviando correo de cancelación de admin: ".$e->getMessage());
        }

        return response()->json(['message'=>'Cita cancelada por el administrador.']);
    }
}

