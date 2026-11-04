@extends('layouts.app')

@section('titulo', 'Registro')

@section('contenido')
<div class="auth-split">
    <aside class="auth-lado">
        <span class="auth-logo"><i data-lucide="graduation-cap"></i></span>
        <h2>Crea tu cuenta en un minuto</h2>
        <p>Únete al piloto y practica estrategias de enseñanza inclusiva con retos cortos.</p>
        <ul class="lado-lista">
            <li><i data-lucide="user-round"></i> Sin nombre real ni correo: solo un seudónimo</li>
            <li><i data-lucide="hand-heart"></i> Participación voluntaria</li>
            <li><i data-lucide="shield-check"></i> Datos anónimos</li>
        </ul>
        <div class="lado-deco" aria-hidden="true">📁 🗄️ 📚 🎡</div>
    </aside>

    <div class="auth-form">
        <h1>Crear cuenta</h1>
        <p class="gris">No pedimos tu nombre real ni tu correo. Usa un seudónimo.</p>

        <form method="POST" action="{{ route('registro') }}">
            @csrf
            <label for="seudonimo">Seudónimo</label>
            <input type="text" id="seudonimo" name="seudonimo" value="{{ old('seudonimo') }}" required autofocus>

            <label for="password">Contraseña (mínimo 6 caracteres)</label>
            <input type="password" id="password" name="password" required>

            <label>¿Qué te motiva más?</label>
            <label class="opcion">
                <input type="radio" name="perfil" value="cumplidor" {{ old('perfil') === 'cumplidor' ? 'checked' : '' }} required>
                <span><strong>Avanzar a mi ritmo:</strong> me gusta ver mi progreso y mantener rachas.</span>
            </label>
            <label class="opcion">
                <input type="radio" name="perfil" value="competitivo" {{ old('perfil') === 'competitivo' ? 'checked' : '' }}>
                <span><strong>Compararme con otros:</strong> me motivan el ranking y los retos entre colegas.</span>
            </label>

            <label class="opcion" style="margin-top:24px">
                <input type="checkbox" name="consentimiento" value="1" {{ old('consentimiento') ? 'checked' : '' }}>
                <span>Acepto participar de forma voluntaria en el piloto. Entiendo que solo se guardan mi seudónimo y mis acciones dentro del juego, que nunca debo escribir datos de estudiantes, y que puedo retirarme y pedir que borren mis datos cuando quiera.</span>
            </label>

            <p style="margin-top:24px"><button class="btn" type="submit">Crear cuenta</button></p>
        </form>
        <p class="gris" style="text-align:center;margin:0">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresar</a>.</p>
    </div>
</div>
@endsection