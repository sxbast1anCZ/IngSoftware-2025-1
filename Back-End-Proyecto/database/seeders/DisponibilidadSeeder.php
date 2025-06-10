<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DisponibilidadMedico;
use Illuminate\Database\Seeder;

class DisponibilidadSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'andres.bravo@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'activo' => true,
            ]);
        } else { echo "No se encontro el usuario con email andres.bravo@clinica.com"; } //ignoren esto es para testear si es que esta seedeando o no

        $user = User::where('email', 'camila.vargas@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'daniel.gomez@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '8:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'felipe.morales@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'isabel.fuentes@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'jorge.soto@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'laura.perez@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'marcela.contreras@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'nicolas.rojas@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'paula.reyes@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'ricardo.diaz@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'sebastian.paredes@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'sofia.mendez@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'tomas.silva@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'activo' => true,
            ]);
        }
        $user = User::where('email', 'veronica.castillo@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'activo' => true,
            ]);
        }
    }
}



