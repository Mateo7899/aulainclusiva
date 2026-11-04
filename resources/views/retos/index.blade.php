@extends('layouts.app')

@section('titulo', 'Mis retos')

@section('contenido')
@php
    $total = $retos->count();
    $hechos = count($aplicados);
    $pct = $total ? round($hechos / $total * 100) : 0;
@endphp

<div class="encabezado">
    <h1>Mis retos</h1>
    <p>Tu historial. Cada día la ruleta te asigna un reto nuevo.</p>
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
        <div class="avance-txt"><strong>{{ $hechos }} de {{ $total }}</strong> retos aplicados en clase</div>
        <div class="barra" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
            <span style="width: {{ $pct }}%"></span>
        </div>
    </div>
</section>

<div class="retos">
    @foreach($retos as $reto)
        @php
            $practicado = in_array($reto->id, $practicados);
            $esHoy = $reto->id === $hoyId;
        @endphp
        <article class="reto">
            <span class="num">{{ $loop->iteration }}</span>
            <h3>{{ $reto->titulo }}</h3>
            <div class="etiquetas">
                <span class="etiqueta"><i data-lucide="gamepad-2" style="width:16px;height:16px"></i> {{ $reto->nombre_actividad }}</span>
                @if(in_array($reto->id, $aplicados))
                    <span class="etiqueta ok">Aplicado en clase</span>
                @elseif($practicado)
                    <span class="etiqueta pend">Falta aplicarlo</span>
                @elseif($esHoy)
                    <span class="etiqueta pend">Reto de hoy</span>
                @else
                    <span class="etiqueta">Sin descubrir</span>
                @endif
            </div>
            <div class="acciones">
                @if($practicado || $esHoy)
                    <a class="btn {{ $practicado ? 'sec' : '' }}" href="{{ route('retos.show', $reto) }}">
                        <i data-lucide="{{ $practicado ? 'book-open' : 'play' }}"></i>
                        {{ $practicado ? 'Abrir reto' : 'Jugar el de hoy' }}
                    </a>
                @else
                    <button class="btn" type="button" disabled>
                        <i data-lucide="lock"></i> Sale en la ruleta
                    </button>
                @endif
            </div>
        </article>
    @endforeach
</div>
@endsection