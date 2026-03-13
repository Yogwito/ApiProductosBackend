<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@api.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@api.com'],
            [
                'name' => 'Usuario consulta',
                'password' => Hash::make('password'),
                'role' => User::ROLE_USUARIO,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operador@api.com'],
            [
                'name' => 'Operador inventario',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OPERADOR,
            ]
        );
    }
}
