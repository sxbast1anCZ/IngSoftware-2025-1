<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        User::create([
            'name' => 'Sebastian',
            'lastname' => 'Cortez',
            'role_id' => 1,
            'rut' => '215461890',
            'phone' => '+56964690977',
            'email' => 'test@test.com',
            'password' => bcrypt('123456'),
            'enabled' => true,
        ]);

        User::create([
            'name' => 'Cristian',
            'lastname' => 'Alvarez',
            'role_id' => 3,
            'rut' => '209723735',
            'phone' => '+56966699642',
            'email' => 'test2@test.com',
            'password' => bcrypt('123456'),
            'enabled' => true,
        ]);

         User::create([
            'name' => 'Antonia',
            'lastname' => 'Flores',
            'role_id' => 2,
            'rut' => '212027987',
            'phone' => '+56949989231',
            'email' => 'test3@test.com',
            'password' => bcrypt('123456'),
            'enabled' => true,
        ]);

    }
}
