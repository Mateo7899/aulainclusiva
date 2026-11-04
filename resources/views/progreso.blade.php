@extends('layouts.app')

@section('titulo', 'Mi progreso')

@section('contenido')
@php
    $meta = $puntos < 50 ? 50 : ($puntos < 150 ? 150 : null);
    $base = $puntos < 50 ? 0 : ($puntos < 150 ? 50 : 150);
    $pct = $meta ? round(($puntos - $base) / ($meta - $base) * 100) : 100;
@endphp

<div class="encabezado">
    <h1>Mi progreso</h1>
    <p>Así vas avanzando como docente inclusivo.</p>
</div>

<section class="tarjeta">
    <div class="grid">
        <div class="dato">
            <span class="ico"><i data-lucide="star"></i></span>
            <strong>{{ $puntos }}</strong><span>puntos</span>
        </div>
        <div class="dato">
            <span class="ico"><i data-lucide="award"></i></span>
            <strong>{{ $nivel }}</strong><span>nivel</span>
        </div>
        <div class="dato">
            <span class="ico"><i data-lucide="flame"></i></span>
            <strong>{{ $racha }}</strong><span>{{ $racha === 1 ? 'día' : 'días' }} de racha</span>
        </div>
    </div>

    <div class="avance">
        <div class="avance-txt">
            @if($meta)
                Te faltan <strong>{{ $meta - $puntos }} puntos</strong> para el siguiente nivel.
            @else
                <strong>¡Alcanzaste el nivel máximo!</strong>
            @endif
        </div>
        <div class="barra" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
            <span style="width: {{ $pct }}%"></span>
        </div>
        <p class="gris" style="margin-top:8px">Niveles: Explorador (0-49), Practicante (50-149), Docente inclusivo (150 o más).</p>
    </div>
</section>

<section class="tarjeta">
    <h2>Insignias</h2>
    <div class="insignias">
        @foreach($insignias as $insignia)
            @php $tiene = in_array($insignia->id, $obtenidas); @endphp
            <div class="insignia {{ $tiene ? 'obtenida' : '' }}">
                <span class="medalla"><i data-lucide="{{ $tiene ? 'award' : 'lock' }}"></i></span>
                <div>
                    <strong>{{ $insignia->nombre }}</strong>
                    <span class="gris">{{ $insignia->descripcion }}</span>
                    <span class="etiqueta {{ $tiene ? 'ok' : '' }}" style="margin-top:4px">{{ $tiene ? 'Obtenida' : 'Pendiente' }}</span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="tarjeta">
    <h2>Últimos movimientos</h2>
    @if($ultimos->isEmpty())
        <p class="gris" style="margin:0">Aún no tienes movimientos. ¡Empieza con tu primer reto!</p>
    @else
        <div class="tabla-wrap">
            <table>
                <tr><th>Fecha</th><th>Reto</th><th>Tipo</th><th>Puntos</th></tr>
                @foreach($ultimos as $evento)
                    <tr>
                        <td>{{ $evento->created_at->format('d/m H:i') }}</td>
                        <td>{{ $evento->reto->titulo ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', $evento->tipo) }}</td>
                        <td><strong>+{{ $evento->puntos }}</strong></td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif
</section>
@endsection