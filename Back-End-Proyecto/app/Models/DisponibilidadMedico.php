<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisponibilidadMedico extends Model
{
    use HasFactory;

    protected $table = 'disponibilidad_medicos';

    protected $fillable = [
        'user_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'activo',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin'    => 'datetime:H:i',
        'activo'      => 'boolean',
    ];

    /**
     * Relación con el modelo User (representa al médico)
     */
    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accesor para obtener nombre del día
     */
    public function getNombreDiaSemanaAttribute()
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        return $dias[$this->dia_semana] ?? 'Desconocido';
    }

    /**
     * Scope para filtrar por disponibilidad activa
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
