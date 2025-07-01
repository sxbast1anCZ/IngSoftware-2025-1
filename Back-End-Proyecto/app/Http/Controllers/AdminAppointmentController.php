<?php

namespace App\Http\Controllers\Admin;

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


class AdminAppointmentController extends Controller
{
    /**
     * Listar todas las citas (con filtros opcionales).
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status'    => 'nullable|in:pendiente,confirmada,cancelada,all',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->first()], 422);
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
     * Actualizar campos permitidos de la cita.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'sometimes|required|date_format:Y-m-d H:i|after_or_equal:now',
            'doctor_id'    => 'sometimes|required|numeric|exists:users,id',
            'reason'       => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->first()], 422);
        }

        // No permitir modificación si ya pasó o está cancelada
        if ($appointment->status === Appointment::STATUS_CANCELADA
            || $appointment->scheduled_at->isPast()
        ) {
            return response()->json(['error'=>'No se puede modificar esta cita.'], 422);
        }

        $appointment->fill($request->only(['scheduled_at','doctor_id','reason']));
        $appointment->save();

        return response()->json([
            'message'     => 'Cita actualizada correctamente.',
            'appointment' => $appointment->fresh(),
        ]);
    }

    /**
     * Cancelar la cita y notificar al paciente.
     */
    public function cancel(Appointment $appointment)
    {
        if ($appointment->status === Appointment::STATUS_CANCELADA) {
            return response()->json(['error'=>'La cita ya está cancelada.'], 422);
        }

        $appointment->status = Appointment::STATUS_CANCELADA;
        $appointment->save();

        // Enviar correo al paciente
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
            Log::error("Error mail admin cancel: ".$e->getMessage());
        }

        return response()->json(['message'=>'Cita cancelada por el administrador.']);
    }
}

