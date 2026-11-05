<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $clave = config('app.admin_password');

        if (empty($clave)) {
            $this->command?->warn('ADMIN_PASSWORD no está definida: no se creó el administrador.');
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@aulainclusiva.local'],
            [
                'name' => 'admin',
                'password' => Hash::make($clave),
                'rol' => 'administrador',
                'perfil' => null,
                'visible_ranking' => false,
            ]
        );
    }
}