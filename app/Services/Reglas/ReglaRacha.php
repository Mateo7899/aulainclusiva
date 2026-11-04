<?php

namespace App\Services\Reglas;

class ReglaRacha implements ReglaPuntos
{
    public function tipo(): string
    {
        return 'bonus_racha';
    }

    /** +15 si lleva 2 o más días seguidos aplicando retos. */
    public function calcular(array $contexto): int
    {
        return (($contexto['racha'] ?? 0) >= 2) ? 15 : 0;
    }
}