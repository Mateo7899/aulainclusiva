<?php

namespace App\Services\Reglas;

class ReglaPractica implements ReglaPuntos
{
    public function tipo(): string
    {
        return 'practica';
    }

    /** +10 si completó la actividad sin errores, +5 si tuvo que corregir. */
    public function calcular(array $contexto): int
    {
        return (($contexto['errores'] ?? 0) === 0) ? 10 : 5;
    }
}