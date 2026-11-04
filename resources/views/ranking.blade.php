@extends('layouts.app')

@section('titulo', 'Ranking')

@section('contenido')
<div class="encabezado">
    <h1>Ranking</h1>
    <p>Los 10 docentes con más puntos. Es opcional y puedes ocultarte cuando quieras.</p>
</div>

<section class="tarjeta">
    @if($top->isEmpty())
        <p class="gris" style="margin:0">Todavía no hay jugadores en el ranking.</p>
    @else
        <div class="tabla-wrap">
            <table>
                <tr><th>#</th><th>Seudónimo</th><th>Puntos</th></tr>
                @foreach($top as $jugador)
                    <tr class="{{ $jugador->id === $user->id ? 'yo' : '' }}">
                        <td>{{ $loop->iteration === 1 ? '🥇' : ($loop->iteration === 2 ? '🥈' : ($loop->iteration === 3 ? '🥉' : $loop->iteration)) }}</td>
                        <td>{{ $jugador->name }}{{ $jugador->id === $user->id ? ' (tú)' : '' }}</td>
                        <td>{{ (int) $jugador->eventos_sum_puntos }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <form method="POST" action="{{ route('ranking.visibilidad') }}" style="margin-top:24px">
        @csrf
        <button class="btn sec" type="submit">
            {{ $user->visible_ranking ? 'Dejar de aparecer en el ranking' : 'Aparecer en el ranking' }}
        </button>
    </form>
</section>
@endsection