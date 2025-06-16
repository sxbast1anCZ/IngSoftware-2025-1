<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'doctor', 'patient']; //Actualicé el seeder para que no nos salga el error de intentar ingresar roles duplicados.

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
