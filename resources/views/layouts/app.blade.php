<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', config('app.name', 'AulaVirtual'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .navbar-logo {
            font-family: "Playfair Display", Georgia, serif;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -1px;
            text-decoration: none;
            white-space: nowrap;
        }

        .logo-aula { color: #21157c; }

        .logo-virtual { color: #587be1; }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 26px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            font-family: "DM Sans", sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #4763a5;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .nav-link:hover { color: #2d4b99; }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-name {
            font-family: "DM Sans", sans-serif;
            font-size: 15px;
            font-weight: 500;
            color: #50669d;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border: none;
            border-radius: 24px;
            background: linear-gradient(100deg, #f03458, #f8486b);
            color: #ffffff;
            font-family: "DM Sans", sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(239, 58, 91, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(239, 58, 91, 0.30);
        }

        .logout-btn i { font-size: 14px; }
    </style>

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
            <nav class="max-w-7xl mx-auto pl-8 sm:pl-14 lg:pl-20 pr-5 sm:pr-8 lg:pr-10 flex items-center justify-between h-16">
                <div class="flex items-center gap-24">
                    <a href="{{ route($dashboard) }}" class="navbar-logo"><span class="logo-aula">Aula</span><span class="logo-virtual">Virtual</span></a>
                    <ul class="nav-menu hidden md:flex">
                        <li>
                            <a href="{{ route($dashboard) }}" class="nav-link">Dashboard</a>
                        </li>
                        @yield('menu_extra')
                    </ul>
                </div>
                <div class="nav-user">
                    <span class="user-name hidden sm:inline">
                        {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Salir
                        </button>
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