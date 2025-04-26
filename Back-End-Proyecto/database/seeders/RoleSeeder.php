<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Role::create([
            'name' => 'admin', //Role_ID = 1
        ]);
        Role::create([
            'name' => 'paciente', //Role_ID = 2
        ]);
        Role::create([
            'name' => 'doctor', //Role_ID = 3
        ]);


    }
}
