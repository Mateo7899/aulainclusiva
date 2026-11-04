@extends('layouts.app')

@section('titulo', $reto->titulo)

@section('contenido')
<p><a href="{{ route('retos.index') }}">← Volver a mis retos</a></p>

<section class="tarjeta">
    <h1 style="margin-bottom:0">{{ $reto->titulo }}</h1>
    <ol class="pasos">
        <li class="paso hecho"><span class="num">✓</span> Aprende</li>
        <li class="paso {{ $practicado ? 'hecho' : 'actual' }}"><span class="num">{{ $practicado ? '✓' : '2' }}</span> Practica</li>
        <li class="paso {{ $aplicado ? 'hecho' : ($practicado ? 'actual' : '') }}"><span class="num">{{ $aplicado ? '✓' : '3' }}</span> Aplica en clase</li>
    </ol>
</section>

<section class="tarjeta">
    <h2>1. Aprende</h2>
    <p style="margin:0">{{ $reto->microleccion }}</p>
</section>

<section class="tarjeta">
    <h2>2. Practica <span class="etiqueta" style="vertical-align:middle">{{ $reto->nombre_actividad }}</span></h2>
    @if($practicado)
        <p class="gris">Ya ganaste los puntos de esta práctica. Puedes jugarla otra vez sin puntos.</p>
    @else
        <p class="gris">Sin errores ganas +{{ $ptsMax }} puntos. Si te equivocas, ganas +{{ $ptsMin }} y te mostramos qué tener presente para aplicarlo bien.</p>
    @endif

    <div id="actividad"></div>
    <div id="mensaje-act" class="msg-act" role="status" aria-live="polite"></div>
    <div id="fin-act"></div>

    @if(!$practicado)
        <form id="form-practica" method="POST" action="{{ route('retos.practica', $reto) }}">
            @csrf
            <input type="hidden" name="errores" id="errores" value="0">
        </form>
    @endif
</section>

<section class="tarjeta">
    <h2>3. Aplica en clase</h2>

    @if($reto->consejo)
        <div class="consejo">
            <strong>💡 Cómo aplicarlo en clase</strong>
            <p>{{ $reto->consejo }}</p>
            @if($practicado && $errores > 0)
                <p style="margin-bottom:0"><strong>Ten presente:</strong> en la práctica tuviste {{ $errores }} {{ $errores === 1 ? 'error' : 'errores' }}. Repasa la microlección de arriba antes de aplicarlo.</p>
            @endif
        </div>
    @endif

    <p class="gris">
        Si lo aplicas dentro de los 3 días siguientes a la práctica, ganas un bono de +10 puntos.
        Si aplicas retos varios días seguidos, ganas un bono de racha.
    </p>
    <form method="POST" action="{{ route('retos.aplicar', $reto) }}">
        @csrf
        <button class="btn acento" type="submit" {{ (!$practicado || $aplicado) ? 'disabled' : '' }}>
            @if($aplicado)
                <i data-lucide="check"></i> Reto aplicado
            @elseif(!$practicado)
                <i data-lucide="lock"></i> Completa la práctica para desbloquear
            @else
                <i data-lucide="check"></i> La apliqué en clase (+20 puntos)
            @endif
        </button>
    </form>
</section>

<script>
    const tipo = @json($reto->tipo_actividad);
    const datos = @json($reto->actividad);
    const yaPracticado = @json($practicado);
    const PTS_MAX = @json($ptsMax);
    const PTS_MIN = @json($ptsMin);
    const CONSEJO = @json($reto->consejo);
    const contenedor = document.getElementById('actividad');
    let errores = 0;
    const retro = [];

    function anotar(texto) {
        if (texto && !retro.includes(texto)) retro.push(texto);
    }

    function mezclar(lista) {
        const b = lista.slice();
        for (let i = b.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [b[i], b[j]] = [b[j], b[i]];
        }
        return b;
    }

    function el(etiqueta, clase, texto) {
        const e = document.createElement(etiqueta);
        if (clase) e.className = clase;
        if (texto !== undefined) e.textContent = texto;
        return e;
    }

    function mensaje(texto, ok) {
        const m = document.getElementById('mensaje-act');
        m.textContent = texto;
        m.className = 'msg-act ' + (texto ? (ok ? 'bien' : 'mal') : '');
    }

    function terminar() {
        const f = document.getElementById('fin-act');
        f.innerHTML = '';
        const caja = el('div', 'resultado visible');

        if (yaPracticado) {
            caja.append(el('h3', '', '🎉 ¡Actividad completada!'));
            caja.append(el('p', '', 'Esta práctica ya te dio puntos.'));
            f.append(caja);
            return;
        }

        const perfecto = errores === 0;
        caja.append(el('h3', '', perfecto ? '🌟 ¡Perfecto, sin errores!' : '💪 ¡Actividad completada!'));
        caja.append(el('p', '', perfecto
            ? 'Ganas todos los puntos de esta práctica: +' + PTS_MAX + '.'
            : 'Tuviste ' + errores + (errores === 1 ? ' error' : ' errores') + ', así que ganas +' + PTS_MIN + ' puntos en lugar de +' + PTS_MAX + '.'));

        if (!perfecto && retro.length) {
            caja.append(el('p', '', 'Para aplicarlo bien en clase, ten presente:'));
            const ul = el('ul');
            retro.forEach(t => ul.append(el('li', '', t)));
            caja.append(ul);
        }

        if (CONSEJO) {
            caja.append(el('p', '', '💡 Cómo aplicarlo en clase: ' + CONSEJO));
        }

        const b = el('button', 'btn acento', 'Guardar mis puntos y continuar');
        b.type = 'button';
        b.onclick = () => {
            b.disabled = true;
            document.getElementById('errores').value = errores;
            document.getElementById('form-practica').submit();
        };
        caja.append(b);

        f.append(caja);
        caja.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ---------- ¿Qué haces? ---------- */
    function escenario(d) {
        contenedor.append(el('div', 'escena', d.escena), el('p', 'situacion', d.situacion));
        const lista = el('div', 'opciones');
        mezclar(d.opciones).forEach(op => {
            const b = el('button', 'op', op.texto);
            b.type = 'button';
            b.onclick = () => {
                if (op.correcta) {
                    b.classList.add('ok');
                    lista.querySelectorAll('button').forEach(x => x.disabled = true);
                    mensaje('✅ ' + op.feedback, true);
                    if (errores > 0) anotar(op.feedback);
                    terminar();
                } else {
                    b.classList.add('mal');
                    b.disabled = true;
                    errores++;
                    mensaje('❌ ' + op.feedback + ' Prueba con otra opción.', false);
                }
            };
            lista.append(b);
        });
        contenedor.append(lista);
    }

    /* ---------- Completa la frase ---------- */
    function completar(d) {
        const p = el('p', 'frase');
        const selects = [];
        d.frase.split(/(\{\d+\})/).forEach(parte => {
            const m = parte.match(/^\{(\d+)\}$/);
            if (m) {
                const hueco = d.huecos[parseInt(m[1], 10)];
                const s = el('select');
                s.append(new Option('elige…', ''));
                mezclar(hueco.opciones).forEach(o => s.append(new Option(o, o)));
                selects.push({ s, hueco });
                p.append(s);
            } else {
                p.append(document.createTextNode(parte));
            }
        });
        const b = el('button', 'btn', 'Comprobar');
        b.type = 'button';
        b.onclick = () => {
            let todoBien = true, hayVacio = false;
            selects.forEach(({ s, hueco }) => {
                s.classList.remove('ok', 'mal');
                if (!s.value) { hayVacio = true; return; }
                if (s.value === hueco.correcta) s.classList.add('ok');
                else { s.classList.add('mal'); todoBien = false; }
            });
            if (hayVacio) { mensaje('Completa todos los huecos antes de comprobar.', false); return; }
            if (todoBien) {
                selects.forEach(({ s }) => s.disabled = true);
                b.disabled = true;
                mensaje('✅ ' + d.explicacion, true);
                terminar();
            } else {
                errores++;
                anotar(d.explicacion);
                mensaje('❌ Hay respuestas por corregir (en rojo). Inténtalo otra vez.', false);
            }
        };
        contenedor.append(p, b);
    }

    /* ---------- Ordena los pasos ---------- */
    function ordenar(d) {
        contenedor.append(el('p', 'situacion', d.instruccion));
        const banco = el('div', 'opciones');
        const tuOrden = el('ol', 'orden');
        const elegidos = [];
        let hecho = false;

        const reiniciar = () => {
            if (hecho) return;
            elegidos.length = 0;
            tuOrden.innerHTML = '';
            banco.querySelectorAll('button').forEach(x => x.disabled = false);
        };

        mezclar(d.pasos.map((t, i) => ({ t, i }))).forEach(paso => {
            const b = el('button', 'op', paso.t);
            b.type = 'button';
            b.onclick = () => {
                b.disabled = true;
                elegidos.push(paso.i);
                tuOrden.append(el('li', '', paso.t));
                if (elegidos.length === d.pasos.length) {
                    if (elegidos.every((v, k) => v === k)) {
                        hecho = true;
                        mensaje('✅ ' + d.explicacion, true);
                        terminar();
                    } else {
                        errores++;
                        anotar(d.explicacion);
                        mensaje('❌ El orden no es correcto. Se reinició, inténtalo otra vez.', false);
                        setTimeout(reiniciar, 1200);
                    }
                }
            };
            banco.append(b);
        });

        const r = el('button', 'btn sec', 'Reiniciar');
        r.type = 'button';
        r.onclick = reiniciar;

        contenedor.append(
            el('p', 'gris', 'Toca los pasos en el orden correcto:'), banco,
            el('p', 'gris', 'Tu orden:'), tuOrden, r
        );
    }

    /* ---------- Mito o realidad / Clasifica ---------- */
    function clasificar(d) {
        if (d.instruccion) contenedor.append(el('p', 'situacion', d.instruccion));
        const lista = mezclar(d.tarjetas);
        let i = 0;
        const cuenta = el('p', 'gris');
        const tarjeta = el('div', 'tarjeta-clas');
        const botones = el('div', 'opciones fila');
        const sig = el('button', 'btn sec', 'Siguiente →');
        sig.type = 'button';
        sig.style.display = 'none';

        function mostrar() {
            tarjeta.textContent = lista[i].texto;
            cuenta.textContent = 'Tarjeta ' + (i + 1) + ' de ' + lista.length;
            botones.querySelectorAll('button').forEach(x => x.disabled = false);
            sig.style.display = 'none';
            mensaje('', true);
        }

        function responder(k) {
            const t = lista[i];
            botones.querySelectorAll('button').forEach(x => x.disabled = true);
            if (k === t.categoria) {
                mensaje('✅ ' + t.explicacion, true);
            } else {
                errores++;
                anotar(t.explicacion);
                mensaje('❌ No exactamente. ' + t.explicacion, false);
            }
            if (i === lista.length - 1) {
                terminar();
            } else {
                sig.style.display = 'inline-flex';
            }
        }

        sig.onclick = () => { i++; mostrar(); };

        d.categorias.forEach((c, k) => {
            const b = el('button', 'op', c);
            b.type = 'button';
            b.style.textAlign = 'center';
            b.onclick = () => responder(k);
            botones.append(b);
        });

        contenedor.append(cuenta, tarjeta, botones, sig);
        mostrar();
    }

    /* ---------- Une las parejas ---------- */
    function emparejar(d) {
        contenedor.append(el('p', 'situacion', d.instruccion));
        const pares = el('div', 'pares');
        const izq = el('div', 'col');
        const der = el('div', 'col');
        let seleccionado = null;
        let hechos = 0;

        d.parejas.forEach((p, k) => {
            const b = el('button', 'op', p.izq);
            b.type = 'button';
            b.dataset.k = k;
            b.onclick = () => {
                izq.querySelectorAll('.sel').forEach(x => x.classList.remove('sel'));
                b.classList.add('sel');
                seleccionado = b;
            };
            izq.append(b);
        });

        mezclar(d.parejas.map((p, k) => ({ t: p.der, k }))).forEach(p => {
            const b = el('button', 'op', p.t);
            b.type = 'button';
            b.onclick = () => {
                if (!seleccionado) {
                    mensaje('Primero toca un elemento de la columna izquierda.', false);
                    return;
                }
                const k = parseInt(seleccionado.dataset.k, 10);
                if (k === p.k) {
                    seleccionado.classList.remove('sel');
                    seleccionado.classList.add('ok');
                    seleccionado.disabled = true;
                    b.classList.add('ok');
                    b.disabled = true;
                    seleccionado = null;
                    hechos++;
                    mensaje('✅ ¡Pareja correcta!', true);
                    if (hechos === d.parejas.length) terminar();
                } else {
                    errores++;
                    anotar('«' + d.parejas[k].izq + '» se relaciona con: ' + d.parejas[k].der + '.');
                    b.classList.add('mal');
                    setTimeout(() => b.classList.remove('mal'), 700);
                    mensaje('❌ Esa no es la pareja. Intenta otra vez.', false);
                }
            };
            der.append(b);
        });

        pares.append(izq, der);
        contenedor.append(pares);
    }

    const juegos = { escenario, completar, ordenar, mito: clasificar, clasificar, emparejar };
    (juegos[tipo] || escenario)(datos);
</script>
@endsection