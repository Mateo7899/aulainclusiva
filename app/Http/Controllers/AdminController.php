<?php

namespace App\Http\Controllers;

use App\Services\ServicioIndicadores;

class AdminController extends Controller
{
    public function indicadores(ServicioIndicadores $indicadores)
    {
        abort_unless(auth()->user()->esAdministrador(), 403);

        return view('admin.indicadores', [
            'porcentaje' => $indicadores->porcentajeRetosAplicados(),
            'aciertos' => $indicadores->porcentajeAciertoPrimerIntento(),
            'activosPorDia' => $indicadores->usuariosActivosPorDia(),
            'jugadores' => $indicadores->totalJugadores(),
            'eventos' => $indicadores->totalEventos(),
        ]);
    }
}