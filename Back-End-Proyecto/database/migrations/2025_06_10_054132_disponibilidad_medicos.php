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
        Schema::create('disponibilidad_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->unsignedTinyInteger('dia_semana')
                  ->comment('1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->decimal('precio', 8, 2); 
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'dia_semana', 'activo']); 
        });
    }

    /**
     * Eliminar las migraciones
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_medicos');
    }
};

