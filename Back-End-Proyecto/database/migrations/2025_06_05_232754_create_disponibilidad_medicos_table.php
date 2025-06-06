<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*Esta tabla es un formato que deberíamos 
        */
        Schema::create('disponibilidad_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('dia_semana')->comment('1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'dia_semana', 'activo']);
            $table->index(['dia_semana', 'activo']);
        });
    }

    /**
     * Revertir alguna migración por cualquier cosa que pueda salir mal.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_medicos');
    }
};
