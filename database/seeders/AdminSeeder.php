<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@aulainclusiva.local'],
            [
                'name' => 'admin',
                'password' => Hash::make('Admin2026!'),
                'rol' => 'administrador',
                'perfil' => null,
                'visible_ranking' => false,
            ]
        );
    }
}