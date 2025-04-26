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

        ]);

    }
}
