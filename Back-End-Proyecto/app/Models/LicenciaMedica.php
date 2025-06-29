<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenciaMedica extends Model
{
    use HasFactory;

    protected $table = 'licencias_medicas'; // <--- Asegúrate que coincide con tu migración real

    protected $fillable = [
        'dias',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'diagnostico_id',
    ];

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class);
    }
}

