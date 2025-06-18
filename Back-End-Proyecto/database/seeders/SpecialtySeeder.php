<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

    $especialidades = [
        'Medicina General',
        'Cardiología',
        'Pediatría',
        'Traumatología',
        'Dermatología',
        'Psiquiatría',
        'Ginecología',
    ];

    foreach ($especialidades as $nombre) {
        Specialty::create(['name' => $nombre]);
    }

    }





}
