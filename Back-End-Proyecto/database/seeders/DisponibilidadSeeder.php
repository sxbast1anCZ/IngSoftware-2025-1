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
                'precio' => 30000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'precio' => 30000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 30000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'precio' => 30000,
                'activo' => true,
            ]);
        } 

        $user = User::where('email', 'camila.vargas@clinica.com')->first();
        if ($user) {
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 1,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 44000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 44000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'precio' => 44000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '11:00',
                'precio' => 44000,
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
                'precio' => 34000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '8:00',
                'hora_fin' => '11:00',
                'precio' => 34000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'precio' => 34000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'precio' => 34000,
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
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'precio' => 41000,
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
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 41000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 41000,
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
                'precio' => 48000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'precio' => 48000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 48000,
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
                'precio' => 33000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'precio' => 33000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 33000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'precio' => 33000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 33000,
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
                'precio' => 32000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '9:00',
                'hora_fin' => '12:00',
                'precio' => 32000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'precio' => 32000,
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
                'precio' => 37000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 37000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 37000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 37000,
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
                'precio' => 43000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 2,
                'hora_inicio' => '9:00',
                'hora_fin' => '13:00',
                'precio' => 43000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'precio' => 43000,
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
                'precio' => 43000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 43000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'precio' => 43000,
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
                'precio' => 36000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 36000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 36000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 36000,
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
                'precio' => 43000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 43000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '12:00',
                'precio' => 43000,
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
                'precio' => 45000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '12:00',
                'precio' => 45000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 4,
                'hora_inicio' => '10:00',
                'hora_fin' => '14:00',
                'precio' => 45000,
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
                'precio' => 35000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 3,
                'hora_inicio' => '8:00',
                'hora_fin' => '10:00',
                'precio' => 35000,
                'activo' => true,
            ]);
            DisponibilidadMedico::create([
                'user_id' => $user->id,
                'dia_semana' => 5,
                'hora_inicio' => '9:00',
                'hora_fin' => '11:00',
                'precio' => 35000,
                'activo' => true,
            ]);
        }
    }
}



