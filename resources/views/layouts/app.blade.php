<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'AulaInclusiva') · AulaInclusiva</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Lexend:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<a class="saltar" href="#principal">Saltar al contenido</a>

<header class="topbar">
    <div class="topbar-in">
        <a class="marca" href="{{ url('/') }}">
            <span class="logo"><i data-lucide="graduation-cap"></i></span>
            AulaInclusiva
        </a>

        @auth
            <nav class="nav" aria-label="Principal">
                @if(auth()->user()->esAdministrador())
                    <a href="{{ route('admin.indicadores') }}" class="{{ request()->routeIs('admin.*') ? 'activo' : '' }}">
                        <i data-lucide="layout-dashboard"></i> Indicadores
                    </a>
                @else
                    <a href="{{ route('ruleta') }}" class="{{ request()->routeIs('ruleta*') ? 'activo' : '' }}">
                        <i data-lucide="sparkles"></i> Ruleta
                    </a>
                    <a href="{{ route('retos.index') }}" class="{{ request()->routeIs('retos.*') ? 'activo' : '' }}">
                        <i data-lucide="book-open"></i> Mis retos
                    </a>
                    <a href="{{ route('progreso') }}" class="{{ request()->routeIs('progreso') ? 'activo' : '' }}">
                        <i data-lucide="trending-up"></i> Mi progreso
                    </a>
                    <a href="{{ route('ranking') }}" class="{{ request()->routeIs('ranking') ? 'activo' : '' }}">
                        <i data-lucide="trophy"></i> Ranking
                    </a>
                @endif
                <span class="usuario">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit"><i data-lucide="log-out"></i> Salir</button>
                </form>
            </nav>
        @endauth
    </div>
</header>

<main id="principal" class="contenedor">
    @if(session('ok'))
        <div class="alerta ok" role="status"><i data-lucide="circle-check"></i><span>{{ session('ok') }}</span></div>
    @endif
    @if(session('error'))
        <div class="alerta error" role="alert"><i data-lucide="circle-alert"></i><span>{{ session('error') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alerta error" role="alert">
            <i data-lucide="circle-alert"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @yield('contenido')
</main>

<footer class="pie">
    <p><strong>AulaInclusiva</strong> · Proyecto de aula de Ingeniería de Software 2</p>
    <p>Piloto con docentes de gestión documental · Participación voluntaria y datos anónimos</p>
</footer>

<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>if (window.lucide) { lucide.createIcons(); }</script>
</body>
</html>