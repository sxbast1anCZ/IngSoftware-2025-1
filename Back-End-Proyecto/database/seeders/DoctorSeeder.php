<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {

        User::create([
            'name'       => 'Nicolás',
            'lastname'   => 'Rojas',
            'profession' => 'Medicina General',
            'rut'        => '13805655-4',
            'phone'      => '+56900000000',
            'email'      => 'nicolás.rojas@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Laura',
            'lastname'   => 'Pérez',
            'profession' => 'Medicina General',
            'rut'        => '11529652-3',
            'phone'      => '+56900000000',
            'email'      => 'laura.pérez@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Tomás',
            'lastname'   => 'Silva',
            'profession' => 'Medicina General',
            'rut'        => '17080772-3',
            'phone'      => '+56900000000',
            'email'      => 'tomás.silva@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Paula',
            'lastname'   => 'Reyes',
            'profession' => 'Medicina General',
            'rut'        => '19538070-8',
            'phone'      => '+56900000000',
            'email'      => 'paula.reyes@clinica.com',
            'password'   => bcrypt(123456),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Felipe',
            'lastname'   => 'Morales',
            'profession' => 'Medicina General',
            'rut'        => '16010194-6',
            'phone'      => '+56900000000',
            'email'      => 'felipe.morales@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Isabel',
            'lastname'   => 'Fuentes',
            'profession' => 'Pediatría',
            'rut'        => '17401793-K',
            'phone'      => '+56900000000',
            'email'      => 'isabel.fuentes@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Jorge',
            'lastname'   => 'Soto',
            'profession' => 'Pediatría',
            'rut'        => '24888985-3',
            'phone'      => '+56900000000',
            'email'      => 'jorge.soto@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Camila',
            'lastname'   => 'Vargas',
            'profession' => 'Dermatología',
            'rut'        => '9425636-4',
            'phone'      => '+56900000000',
            'email'      => 'camila.vargas@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Ricardo',
            'lastname'   => 'Díaz',
            'profession' => 'Dermatología',
            'rut'        => '9986323-4',
            'phone'      => '+56900000000',
            'email'      => 'ricardo.díaz@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Verónica',
            'lastname'   => 'Castillo',
            'profession' => 'Ginecología',
            'rut'        => '22190218-1',
            'phone'      => '+56900000000',
            'email'      => 'verónica.castillo@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Daniel',
            'lastname'   => 'Gómez',
            'profession' => 'Ginecología',
            'rut'        => '16798845-8',
            'phone'      => '+56900000000',
            'email'      => 'daniel.gómez@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Marcela',
            'lastname'   => 'Contreras',
            'profession' => 'Traumatología',
            'rut'        => '20639337-8',
            'phone'      => '+56900000000',
            'email'      => 'marcela.contreras@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Sebastián',
            'lastname'   => 'Paredes',
            'profession' => 'Cardiología',
            'rut'        => '15365583-9',
            'phone'      => '+56900000000',
            'email'      => 'sebastián.paredes@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Andrés',
            'lastname'   => 'Bravo',
            'profession' => 'Cardiología',
            'rut'        => '24544826-0',
            'phone'      => '+56900000000',
            'email'      => 'andrés.bravo@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
    
        User::create([
            'name'       => 'Sofía',
            'lastname'   => 'Méndez',
            'profession' => 'Psiquiatría',
            'rut'        => '7972633-8',
            'phone'      => '+56900000000',
            'email'      => 'sofía.méndez@clinica.com',
            'password'   => bcrypt(Str::random(10)),
            'role_id'    => 3,
            'enabled'    => true,
        ]);
        }
}