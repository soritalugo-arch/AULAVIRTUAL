<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', config('app.name', 'AulaVirtual'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite([
            'resources/css/app.css',
            'resources/css/layouts/app.css',
            'resources/js/layouts/app.js',
        ])
    @endif

    @stack('styles')
</head>
<body class="bg-[#f3f7fd] min-h-screen">
    @auth
        @php
            $dashboard = auth()->user()->roles->contains('nombre', 'admin')
                ? 'admin.dashboard'
                : (auth()->user()->roles->contains('nombre', 'profesor')
                    ? 'profesor.dashboard'
                    : 'estudiante.dashboard');
        @endphp
        <header class="navbar-card">
            <div class="nav-left">
                <a href="{{ route($dashboard) }}" class="navbar-logo">
                    <span class="logo-icon">
                        <svg viewBox="0 0 140 105" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <defs>
                                <linearGradient id="graduationGradient" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#4c5bc3"/>
                                    <stop offset="100%" stop-color="#6e94ee"/>
                                </linearGradient>
                            </defs>
                            <polygon points="70,4 136,34 70,65 4,34" fill="url(#graduationGradient)"/>
                            <circle cx="70" cy="34" r="3" fill="white"/>
                            <path d="M26 49 L26 72 Q26 78 32 81 L64 96 Q70 99 76 96 L108 81 Q114 78 114 72 L114 49 L70 69 Z" fill="url(#graduationGradient)"/>
                            <path d="M26 49 L70 70 L114 49" fill="none" stroke="#ffffff" stroke-width="4" opacity="0.9"/>
                            <path d="M125 35 L125 61" fill="none" stroke="#5a73d2" stroke-width="4" stroke-linecap="round"/>
                            <circle cx="125" cy="67" r="7" fill="#607ddc"/>
                            <path d="M125 73 L125 91" fill="none" stroke="#607ddc" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="logo-word"><span class="logo-aula">Aula</span><span class="logo-virtual">Virtual</span></span>
                </a>
                <div class="nav-divider"></div>
                <ul class="nav-menu" id="mobile-menu">
                    <li class="nav-indicator" aria-hidden="true"></li>
                    <li>
                        <a href="{{ route($dashboard) }}" class="nav-link {{ request()->routeIs($dashboard) ? 'active' : '' }}">Inicio</a>
                    </li>
                    @yield('menu_extra')
                </ul>
            </div>
            <div class="nav-right">
                <a href="{{ route('perfil') }}" class="user enlace-perfil" title="Ver mis datos" aria-label="Mis datos">
                    <div class="user-icon"><i class="fa-solid fa-user"></i></div>
                    <span class="user-name">{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</span>
                </a>
                <div class="nav-divider hidden sm:block"></div>
                <form method="POST" action="{{ route('logout') }}" class="m-0 flex">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Salir</span>
                    </button>
                </form>
                <!-- Botón Toggle -->
                <button id="btn-toggle-menu" class="menu-toggle-btn">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </header>
    @endauth

    @php $claseMain = $__env->yieldContent('clase_main', 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'); @endphp
    <main class="{{ $claseMain }} py-10">
        @yield('contenido')
    </main>

    @stack('scripts')


</body>
</html>