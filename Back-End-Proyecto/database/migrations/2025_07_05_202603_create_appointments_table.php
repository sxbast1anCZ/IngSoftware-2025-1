<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('disponibilidad_id')->nullable()->constrained('disponibilidad_medicos')->nullOnDelete();
            $table->timestamp('scheduled_at');
            $table->integer('duration')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pendiente');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    // Eliminar las claves foráneas si existen para que pueda hacer php artisan migrate:refresh --seed Y QUE NO ME TIRE ERROR AA
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropForeign(['patient_id']);
        $table->dropForeign(['doctor_id']);
    });

    // Luego elimina la tabla de appointments
    Schema::dropIfExists('appointments');
}


};
