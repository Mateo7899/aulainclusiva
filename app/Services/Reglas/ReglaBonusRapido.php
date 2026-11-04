<?php

namespace App\Services\Reglas;

class ReglaBonusRapido implements ReglaPuntos
{
    public function tipo(): string
    {
        return 'bonus_rapido';
    }

    /** +10 si aplicó el reto máximo 3 días después de completar la práctica. */
    public function calcular(array $contexto): int
    {
        $dias = $contexto['dias_desde_practica'] ?? null;

        return ($dias !== null && $dias <= 3) ? 10 : 0;
    }
}