<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', config('app.name', 'AulaVirtual'))</title>

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gray-100 min-h-screen">
    @auth
        @php
            $dashboard = auth()->user()->roles->contains('nombre', 'admin')
                ? 'admin.dashboard'
                : (auth()->user()->roles->contains('nombre', 'profesor')
                    ? 'profesor.dashboard'
                    : 'estudiante.dashboard');
        @endphp
        <header class="bg-white border-b border-gray-200">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route($dashboard) }}" class="text-xl font-bold text-blue-700">AulaVirtual</a>
                    <ul class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700">
                        <li>
                            <a href="{{ route($dashboard) }}" class="hover:text-blue-700">Dashboard</a>
                        </li>
                        @yield('menu_extra')
                    </ul>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="hidden sm:inline text-gray-600">
                        {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700">Salir</button>
                    </form>
                </div>
            </nav>
        </header>
    @endauth

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('contenido')
    </main>
</body>
</html>