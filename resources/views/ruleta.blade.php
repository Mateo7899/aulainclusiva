@extends('layouts.app')

@section('titulo', 'Ruleta del día')

@section('contenido')
@php
    $u = auth()->user();
    $pts = $u->puntosTotales();
    $niv = $u->nivel();
    $rach = app(\App\Services\ServicioPuntos::class)->racha($u);

    $datos = [
        'Un expediente reúne los documentos de un mismo asunto, en el orden en que se fueron produciendo.',
        'Anunciar el tiempo extra para todo el grupo evita que alguien se sienta señalado.',
        'Una consigna dicha y escrita llega a más estudiantes que una que solo se escucha.',
        'Reconocer algo concreto motiva más que un «muy bien» general.',
        'Una pausa de dos minutos puede recuperar la atención de todo el grupo.',
    ];
    $dato = $datos[now()->dayOfYear % count($datos)];
@endphp

<div class="encabezado">
    <h1>Ruleta del día</h1>
    <p>Hola, {{ $u->name }}. Gira la ruleta para descubrir el reto que jugarás hoy.</p>
</div>

<div class="dos-col">
    <section class="tarjeta ruleta-caja">
        @if($retoHoy)
            {{-- Ya giró hoy: se muestra su reto --}}
            <p class="gris" style="margin-bottom:8px">Tu reto de hoy</p>
            <h2>{{ $retoHoy->titulo }}</h2>
            <p>
                <span class="etiqueta">{{ $retoHoy->nombre_actividad }}</span>
                @if($aplicadoHoy)
                    <span class="etiqueta ok">Aplicado en clase</span>
                @elseif($practicadoHoy)
                    <span class="etiqueta pend">Falta aplicarlo</span>
                @endif
            </p>
            <a class="btn" href="{{ route('retos.show', $retoHoy) }}">
                <i data-lucide="{{ $practicadoHoy ? 'book-open' : 'play' }}"></i>
                {{ $practicadoHoy ? 'Abrir reto' : 'Jugar ahora' }}
            </a>
            <p class="gris" style="margin:24px 0 0">Vuelve mañana para girar la ruleta de nuevo.</p>

        @elseif($segmentos->isEmpty())
            <h2>¡Completaste todos los retos!</h2>
            <p>Ya no quedan retos pendientes en la ruleta. Puedes repasar cualquiera desde «Mis retos».</p>
            <a class="btn" href="{{ route('retos.index') }}"><i data-lucide="book-open"></i> Ver mis retos</a>

        @else
            <div class="ruleta-marco">
                <div class="puntero" aria-hidden="true"></div>
                <svg id="rueda" viewBox="-200 -200 400 400" role="img" aria-label="Ruleta con los retos pendientes"></svg>
            </div>

            <button id="btn-girar" class="btn acento" type="button">
                <i data-lucide="sparkles"></i> Girar la ruleta
            </button>

            <div id="resultado" class="resultado" role="status" aria-live="polite"></div>

            <script>
                const segmentos = @json($segmentos);
                const urlGirar = @json(route('ruleta.girar'));
                const token = @json(csrf_token());

                const NS = 'http://www.w3.org/2000/svg';
                const svg = document.getElementById('rueda');
                const R = 190;
                const N = segmentos.length;
                const ang = 360 / N;
                const colores = [['#0B3C49', '#FFFFFF'], ['#14606F', '#FFFFFF'], ['#F4B23E', '#14212B']];

                function punto(r, grados) {
                    const a = (grados - 90) * Math.PI / 180;
                    return [r * Math.cos(a), r * Math.sin(a)];
                }

                const rueda = document.createElementNS(NS, 'g');
                rueda.style.transformOrigin = '0 0';
                svg.append(rueda);

                segmentos.forEach((s, i) => {
                    let c = i % 3;
                    if (i === N - 1 && c === 0 && N > 1) c = 1;   // evita dos segmentos iguales juntos

                    let forma;
                    if (N === 1) {
                        forma = document.createElementNS(NS, 'circle');
                        forma.setAttribute('r', R);
                    } else {
                        const [x1, y1] = punto(R, i * ang);
                        const [x2, y2] = punto(R, (i + 1) * ang);
                        forma = document.createElementNS(NS, 'path');
                        forma.setAttribute('d', `M 0 0 L ${x1} ${y1} A ${R} ${R} 0 ${ang > 180 ? 1 : 0} 1 ${x2} ${y2} Z`);
                    }
                    forma.setAttribute('fill', colores[c][0]);
                    forma.setAttribute('stroke', '#FFFFFF');
                    forma.setAttribute('stroke-width', '3');
                    rueda.append(forma);

                    const medio = i * ang + ang / 2;
                    const [tx, ty] = punto(R * 0.68, medio);
                    const t = document.createElementNS(NS, 'text');
                    t.textContent = s.n;
                    t.setAttribute('x', tx);
                    t.setAttribute('y', ty);
                    t.setAttribute('text-anchor', 'middle');
                    t.setAttribute('dominant-baseline', 'central');
                    t.setAttribute('transform', `rotate(${N === 1 ? 0 : medio} ${tx} ${ty})`);
                    t.setAttribute('fill', colores[c][1]);
                    t.setAttribute('font-size', '34');
                    t.setAttribute('font-weight', '700');
                    t.setAttribute('font-family', 'Lexend, system-ui, sans-serif');
                    rueda.append(t);
                });

                const centro = document.createElementNS(NS, 'circle');
                centro.setAttribute('r', '30');
                centro.setAttribute('fill', '#FFFFFF');
                centro.setAttribute('stroke', '#F4B23E');
                centro.setAttribute('stroke-width', '6');
                rueda.append(centro);

                const boton = document.getElementById('btn-girar');
                const resultado = document.getElementById('resultado');
                let girando = false;

                function aviso(texto) {
                    resultado.className = 'resultado visible';
                    resultado.textContent = texto;
                }

                boton.addEventListener('click', async () => {
                    if (girando) return;
                    girando = true;
                    boton.disabled = true;

                    let datos;
                    try {
                        const resp = await fetch(urlGirar, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        });
                        datos = await resp.json();
                        if (!resp.ok) throw new Error(datos.error || 'Error');
                    } catch (e) {
                        aviso('No se pudo girar la ruleta. Recarga la página e inténtalo otra vez.');
                        girando = false;
                        boton.disabled = false;
                        return;
                    }

                    const indice = segmentos.findIndex(s => s.id === datos.reto_id);
                    const centroSegmento = indice * ang + ang / 2;
                    const desvio = (Math.random() - 0.5) * ang * 0.6;
                    const objetivo = 360 * 5 + (360 - centroSegmento) + desvio;

                    rueda.getBoundingClientRect();   // fuerza el cálculo antes de animar
                    rueda.style.transition = 'transform 5s cubic-bezier(.12, .67, .12, 1)';
                    rueda.style.transform = `rotate(${objetivo}deg)`;

                    const reducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    setTimeout(() => {
                        resultado.className = 'resultado visible';
                        resultado.innerHTML = '';

                        const t1 = document.createElement('p');
                        t1.className = 'gris';
                        t1.textContent = '🎉 ¡Tu reto de hoy es!';
                        const h = document.createElement('h2');
                        h.textContent = datos.titulo;
                        const etiqueta = document.createElement('span');
                        etiqueta.className = 'etiqueta';
                        etiqueta.textContent = datos.tipo;
                        const enlace = document.createElement('a');
                        enlace.className = 'btn';
                        enlace.href = datos.url;
                        enlace.textContent = 'Jugar ahora';
                        enlace.style.marginTop = '16px';

                        resultado.append(t1, h, etiqueta, document.createElement('br'), enlace);
                        enlace.focus();
                    }, reducido ? 300 : 5300);
                });
            </script>
        @endif
    </section>

    <aside class="lateral">
        <section class="tarjeta">
            <h3>Tu progreso</h3>
            <div class="mini"><span><i data-lucide="star"></i> Puntos</span><strong>{{ $pts }}</strong></div>
            <div class="mini"><span><i data-lucide="award"></i> Nivel</span><strong>{{ $niv }}</strong></div>
            <div class="mini"><span><i data-lucide="flame"></i> Racha</span><strong>{{ $rach }} {{ $rach === 1 ? 'día' : 'días' }}</strong></div>
            <a class="btn sec" href="{{ route('progreso') }}" style="width:100%;margin-top:16px">Ver mi progreso</a>
        </section>

        <section class="tarjeta">
            <h3>¿Cómo funciona?</h3>
            <ol class="como">
                <li><span class="num">1</span><div><strong>Gira</strong> la ruleta: te toca un reto al día.</div></li>
                <li><span class="num">2</span><div><strong>Practica</strong> con la actividad. Sin errores ganas +10; si te equivocas, +5 y te damos consejos.</div></li>
                <li><span class="num">3</span><div><strong>Aplícalo en clase</strong> y suma más puntos, bonos y rachas.</div></li>
            </ol>
        </section>

        <section class="tarjeta consejo">
            <strong>💡 Para recordar</strong>
            <p>{{ $dato }}</p>
        </section>
    </aside>
</div>
@endsection