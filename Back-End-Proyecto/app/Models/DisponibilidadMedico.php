<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class DisponibilidadMedico extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'dia_semana', // 1=Lunes, 2=Martes, etc.
        'hora_inicio', 
        'hora_fin',
        'activo'
    ];

    /*Activo sirve como lo que teniamos antes, de usar 0 para usuario deshabilitado y 1 para usuario habilitado
    El resto de variables las trabajo como hora para tener un intervalo de disponibilidad establecido por lo que nos diga el Dr Ivo
    */
    protected $casts = [

    
        'activo' => 'boolean',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i'
    ];

    //Esta función la puse para verificar si especificamente los médicos, aunque la función de identificar un usuario con su role_id debería cumplir el mismo propósito.
    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

// Esta función te debería permitir ver los horarios de un día especifico, por ejemplo, horario del doctor 1 el lunes.
    public function scopePorDia($query, $dia)
    {
        return $query->where('dia_semana', $dia)->where('activo', true);
    }






}