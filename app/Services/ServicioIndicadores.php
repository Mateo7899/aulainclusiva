<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\User;

class ServicioIndicadores
{
    /**
     * Indicador 1: % de prácticas completadas que terminaron aplicadas en clase.
     * Fórmula: (eventos "aplicacion" / eventos "practica") x 100
     */
    public function porcentajeRetosAplicados(): float
    {
        $practicas = Evento::where('tipo', 'practica')->count();

        if ($practicas === 0) {
            return 0.0;
        }

        $aplicaciones = Evento::where('tipo', 'aplicacion')->count();

        return round($aplicaciones / $practicas * 100, 1);
    }

    /**
     * Indicador 2: usuarios activos por día.
     * Fórmula: número de usuarios distintos con al menos un evento ese día.
     */
    public function usuariosActivosPorDia(): array
    {
        return Evento::selectRaw('DATE(created_at) as dia, COUNT(DISTINCT user_id) as usuarios')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(fn ($fila) => ['dia' => $fila->dia, 'usuarios' => (int) $fila->usuarios])
            ->all();
    }

    /**
     * Indicador 3 (complementario): % de prácticas completadas sin errores.
     * Fórmula: (prácticas con 0 errores / prácticas totales) x 100
     */
    public function porcentajeAciertoPrimerIntento(): float
    {
        $practicas = Evento::where('tipo', 'practica')->count();

        if ($practicas === 0) {
            return 0.0;
        }

        $sinErrores = Evento::where('tipo', 'practica')->where('errores', 0)->count();

        return round($sinErrores / $practicas * 100, 1);
    }

    public function totalJugadores(): int
    {
        return User::where('rol', 'jugador')->count();
    }

    public function totalEventos(): int
    {
        return Evento::count();
    }
}