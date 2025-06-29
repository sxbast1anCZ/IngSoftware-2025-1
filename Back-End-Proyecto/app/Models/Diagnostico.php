<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Diagnostico extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'motivo_consulta',
        'diagnostico',
        'tratamiento',
        'notas',
    ];

    public function cita()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function licencia()
    {
        return $this->hasOne(LicenciaMedica::class, 'diagnostico_id');
    }
}



