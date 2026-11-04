<?php

namespace App\Services\Reglas;

class ReglaAplicacion implements ReglaPuntos
{
    public function tipo(): string
    {
        return 'aplicacion';
    }

    public function calcular(array $contexto): int
    {
        return 20;
    }
}