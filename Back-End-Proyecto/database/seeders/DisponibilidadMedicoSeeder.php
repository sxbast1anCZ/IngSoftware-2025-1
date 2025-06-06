<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DisponibilidadMedico;
use Illuminate\Support\Facades\Hash;

class DisponibilidadMedicoSeeder extends Seeder
{
    public function run(): void
    {
        // Primero crear algunos médicos de prueba (role_id = 3)
        $medicos = [
            [
                'name' => 'Dr. Nicolas Rojas',
                'lastname' => 'Rojas',
                'profession' => 'Medicina General',
                'email' => 'nicolas.rojas@clinica.com',
                'password' => Hash::make('password123'),
                'role_id' => 3,
                'enabled' => true,
                'rut' => '13.805.655-4',
                'phone' => '+56987654321'
            ],
            [
                'name' => 'Dra. Laura Perez',
                'lastname' => 'Perez', 
                'profession' => 'Medicina General',
                'email' => 'laura.perez@clinica.com',
                'password' => Hash::make('password123'),
                'role_id' => 3,
                'enabled' => true,
                'rut' => '11.529.652-3',
                'phone' => '+56987654322'
            ],
            [
                'name' => 'Dr. Jorge Soto',
                'lastname' => 'Soto',
                'profession' => 'Pediatría',
                'email' => 'jorge.soto@clinica.com', 
                'password' => Hash::make('password123'),
                'role_id' => 3,
                'enabled' => true,
                'rut' => '24.888.985-3',
                'phone' => '+56987654323'
            ]
        ];

        foreach ($medicos as $medicoData) {
            $medico = User::create($medicoData);
            
            // Crear horarios basados en tu imagen de ejemplo
            if ($medico->name === 'Dr. Nicolas Rojas') {
                // Lunes: 8:00 a 10:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 1, // Lunes
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '10:00:00',
                    'activo' => true
                ]);
                
                // Martes: 9:00 a 11:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 2, // Martes
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '11:00:00',
                    'activo' => true
                ]);
                
                // Jueves: 8:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 4, // Jueves
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
                
                // Viernes: 9:00 a 13:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 5, // Viernes
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '13:00:00',
                    'activo' => true
                ]);
            }
            
            if ($medico->name === 'Dra. Laura Perez') {
                // Lunes: 9:00 a 11:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 1,
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '11:00:00',
                    'activo' => true
                ]);
                
                // Martes: 9:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 2,
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
                
                // Miércoles: 8:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 3,
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
                
                // Jueves: 10:00 a 14:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 4,
                    'hora_inicio' => '10:00:00',
                    'hora_fin' => '14:00:00',
                    'activo' => true
                ]);
                
                // Viernes: 8:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 5,
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
            }
            
            if ($medico->name === 'Dr. Jorge Soto') {
                // Lunes: 9:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 1,
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
                
                // Martes: 9:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 2,
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
                
                // Miércoles: 8:00 a 12:00 hrs
                DisponibilidadMedico::create([
                    'user_id' => $medico->id,
                    'dia_semana' => 3,
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '12:00:00',
                    'activo' => true
                ]);
            }
        }
    }
}