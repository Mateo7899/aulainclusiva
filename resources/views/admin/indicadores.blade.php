@extends('layouts.app')

@section('titulo', 'Indicadores')

@section('contenido')
@php $maximo = max(1, collect($activosPorDia)->max('usuarios') ?? 1); @endphp

<div class="encabezado">
    <h1>Indicadores del piloto</h1>
    <p>Resultados medidos con las fórmulas acordadas con el cliente.</p>
</div>

<section class="tarjeta">
    <div class="grid">
        <div class="dato">
            <span class="ico"><i data-lucide="users"></i></span>
            <strong>{{ $jugadores }}</strong><span>docentes registrados</span>
        </div>
        <div class="dato">
            <span class="ico"><i data-lucide="activity"></i></span>
            <strong>{{ $eventos }}</strong><span>eventos registrados</span>
        </div>
        <div class="dato">
            <span class="ico"><i data-lucide="gauge"></i></span>
            <strong>{{ $porcentaje }}%</strong><span>retos aplicados</span>
        </div>
        <div class="dato">
            <span class="ico"><i data-lucide="check"></i></span>
            <strong>{{ $aciertos }}%</strong><span>práctica sin errores</span>
        </div>
    </div>
</section>

<section class="tarjeta">
    <h2>Indicador 1: % de retos aplicados en clase</h2>
    <p style="font-family:var(--f-titulo);font-size:2rem;font-weight:700;color:var(--teal);margin-bottom:8px">{{ $porcentaje }}%</p>
    <div class="barra" role="progressbar" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ min(100, $porcentaje) }}%"></span></div>
    <p class="gris" style="margin-top:16px;margin-bottom:0">Fórmula: (aplicaciones registradas / prácticas completadas) × 100.</p>
</section>

<section class="tarjeta">
    <h2>Indicador 2: usuarios activos por día</h2>
    @if(empty($activosPorDia))
        <p class="gris">Aún no hay actividad registrada.</p>
    @else
        @foreach($activosPorDia as $fila)
            <div class="fila-barra">
                <span>{{ $fila['dia'] }}</span>
                <div class="barra"><span style="width: {{ round($fila['usuarios'] / $maximo * 100) }}%"></span></div>
                <b>{{ $fila['usuarios'] }}</b>
            </div>
        @endforeach
    @endif
    <p class="gris" style="margin-top:16px;margin-bottom:0">Fórmula: número de usuarios distintos con al menos un evento ese día.</p>
</section>

<section class="tarjeta">
    <h2>Indicador complementario: % de práctica sin errores</h2>
    <p style="font-family:var(--f-titulo);font-size:2rem;font-weight:700;color:var(--teal);margin-bottom:8px">{{ $aciertos }}%</p>
    <div class="barra" role="progressbar" aria-valuenow="{{ $aciertos }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ min(100, $aciertos) }}%"></span></div>
    <p class="gris" style="margin-top:16px;margin-bottom:0">Fórmula: (prácticas completadas con 0 errores / prácticas completadas) × 100.</p>
</section>
@endsection