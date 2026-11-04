<?php

namespace Database\Seeders;

use App\Models\Insignia;
use Illuminate\Database\Seeder;

class InsigniaSeeder extends Seeder
{
    public function run(): void
    {
        $insignias = [
            ['primera_estrategia', 'Primera estrategia', 'Aplicaste tu primer reto en clase.'],
            ['racha_3', 'Racha de 3', 'Aplicaste retos 3 días seguidos.'],
            ['cinco_estrategias', 'Cinco estrategias', 'Aplicaste 5 retos distintos.'],
        ];

        foreach ($insignias as [$clave, $nombre, $descripcion]) {
            Insignia::firstOrCreate(
                ['clave' => $clave],
                ['nombre' => $nombre, 'descripcion' => $descripcion]
            );
        }
    }
}
