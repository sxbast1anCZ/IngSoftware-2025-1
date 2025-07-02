<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    // Constantes para estandarizar valores de los estados q vamos a tener que usar para cuando lo hagamos
    const STATUS_PENDIENTE   = 'pendiente';
    const STATUS_CONFIRMADA  = 'confirmada';
    const STATUS_CANCELADA   = 'cancelada';

    const PAGO_PENDIENTE     = 'pendiente';
    const PAGO_REALIZADO     = 'realizado';

protected $fillable = [
    'patient_id',
    'doctor_id',
    'disponibilidad_id',
    'scheduled_at',
    'duration',
    'price',
    'payment_method',
    'status',
    'reason',
];

/**
 * Para que scheduled_at sea siempre un Carbon
 */
protected $casts = [
    'scheduled_at' => 'datetime',
    'duration'     => 'integer',
    'price'        => 'decimal:2',
];



    /**
     * Relación con el modelo User (médico) para claves foraneas
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Relación con el modelo User (paciente)
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function disponibilidad()
    {
        return $this->belongsTo(DisponibilidadMedico::class, 'disponibilidad_id');
    }


    
     // Método auxiliar para verificar si ya existe una cita en una hora exacta para un médico
     
    public static function existeConflicto($doctorId, $scheduledAt)
    {
        return self::where('doctor_id', $doctorId)
            ->where('scheduled_at', $scheduledAt)
            ->exists();
    }

/**
 * Filtra por estado (o devuelve todos si es 'all').
 */
public function scopeByStatus($query, $status)
{
    if ($status && $status !== 'all') {
        return $query->where('status', $status);
    }
    return $query;
}

/**
 * Sólo citas futuras.
 */
public function scopeFuture($query)
{
    return $query->where('scheduled_at', '>=', now());
}








}

