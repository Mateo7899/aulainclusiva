@extends('layouts.app')

@section('titulo', 'Ingresar')

@section('contenido')
<div class="auth-split">
    <aside class="auth-lado">
        <span class="auth-logo"><i data-lucide="graduation-cap"></i></span>
        <h2>Aprende jugando, enseña mejor</h2>
        <p>Retos cortos para docentes de gestión documental que quieren enseñar de forma más inclusiva.</p>
        <ul class="lado-lista">
            <li><i data-lucide="sparkles"></i> Gira la ruleta y descubre tu reto del día</li>
            <li><i data-lucide="gamepad-2"></i> Practica con actividades interactivas</li>
            <li><i data-lucide="trophy"></i> Gana puntos, rachas e insignias</li>
        </ul>
        <div class="lado-deco" aria-hidden="true">📁 🗄️ 📚 🎡</div>
    </aside>

    <div class="auth-form">
        <h1>Ingresar</h1>
        <p class="gris">Entra con tu seudónimo para continuar.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="seudonimo">Seudónimo</label>
            <input type="text" id="seudonimo" name="seudonimo" value="{{ old('seudonimo') }}" required autofocus>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <p style="margin-top:24px"><button class="btn" type="submit">Entrar</button></p>
        </form>
        <p class="gris" style="text-align:center;margin:0">¿Primera vez? <a href="{{ route('registro') }}">Crea tu cuenta con un seudónimo</a>.</p>
    </div>
</div>
@endsection