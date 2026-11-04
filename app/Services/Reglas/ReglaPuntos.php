<?php

namespace App\Services\Reglas;

interface ReglaPuntos
{
    /** Tipo de evento que genera esta regla. */
    public function tipo(): string;

    /** Puntos que otorga según el contexto. Devuelve 0 si no aplica. */
    public function calcular(array $contexto): int;
}