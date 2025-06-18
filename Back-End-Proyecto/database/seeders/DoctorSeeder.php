<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Specialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            'Medicina General',
            'Pediatría',
            'Dermatología',
            'Ginecología',
            'Traumatología',
            'Cardiología',
            'Psiquiatría',
        ];

        // Crear un mapa nombre => ID desde la tabla specialties
        $especialidadesMap = [];
        foreach ($especialidades as $nombre) {
            $id = Specialty::where('name', $nombre)->value('id');
            if (!$id) {
                throw new \Exception("Especialidad '{$nombre}' no encontrada en la tabla specialties");
            }
            $especialidadesMap[$nombre] = $id;
        }

        $doctores = [
            ['Nicolás', 'Rojas', '13805655-4', 'Medicina General'],
            ['Laura', 'Pérez', '11529652-3', 'Medicina General'],
            ['Tomás', 'Silva', '17080772-3', 'Medicina General'],
            ['Paula', 'Reyes', '19538070-8', 'Medicina General'],
            ['Felipe', 'Morales', '16010194-6', 'Medicina General'],
            ['Isabel', 'Fuentes', '17401793-K', 'Pediatría'],
            ['Jorge', 'Soto', '24888985-3', 'Pediatría'],
            ['Camila', 'Vargas', '9425636-4', 'Dermatología'],
            ['Ricardo', 'Díaz', '9986323-4', 'Dermatología'],
            ['Verónica', 'Castillo', '22190218-1', 'Ginecología'],
            ['Daniel', 'Gómez', '16798845-8', 'Ginecología'],
            ['Marcela', 'Contreras', '20639337-8', 'Traumatología'],
            ['Sebastián', 'Paredes', '15365583-9', 'Cardiología'],
            ['Andrés', 'Bravo', '24544826-0', 'Cardiología'],
            ['Sofía', 'Méndez', '7972633-8', 'Psiquiatría'],
        ];

        foreach ($doctores as [$nombre, $apellido, $rut, $especialidad]) {
            User::create([
                'name'         => $nombre,
                'lastname'     => $apellido,
                'rut'          => $rut,
                'phone'        => '+56900000000',
                'email'        => strtolower(str_replace('áéíóúñ', 'aeioun', $nombre)) . '.' .
                                  strtolower(str_replace('áéíóúñ', 'aeioun', $apellido)) . '@clinica.com',
                'password'     => bcrypt(123456),
                'role_id'      => 2,
                'enabled'      => true,
                'specialty_id' => $especialidadesMap[$especialidad],
            ]);
        }
    }
}
