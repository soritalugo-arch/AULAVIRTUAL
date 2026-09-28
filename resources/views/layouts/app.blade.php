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
        /* ===== NAVBAR: TARJETA FLOTANTE ===== */

        .navbar-card {
            width: calc(100% - 70px);
            height: 90px;
            margin: 22px auto 0;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #dce7fa;
            border-radius: 28px;
            box-shadow: 0 8px 25px rgba(70, 105, 170, 0.10);
            backdrop-filter: blur(10px);
        }

        .nav-left {
            display: flex;
            align-items: center;
        }

        .nav-right {
            display: flex;
            align-items: center;
        }

        .nav-divider {
            width: 1px;
            height: 35px;
            margin: 0 30px;
            background: #e1e7f2;
        }

        .navbar-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -1px;
            text-decoration: none;
            white-space: nowrap;
        }

        .logo-icon {
            display: inline-flex;
            flex-shrink: 0;
            color: #5d7fdf;
        }

        .logo-icon svg {
            width: 37px;
            height: 28px;
        }

        .logo-aula { color: #21157c; }

        .logo-virtual { color: #587be1; }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 35px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            position: relative;
            font-family: "DM Sans", sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #4763a5;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .nav-link:hover { color: #2d4b99; }

        /* Enlace activo: texto azul + línea inferior */
        .nav-link.active {
            color: #2f55c4;
            font-weight: 700;
        }

        .nav-link.active::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -8px;
            width: 100%;
            height: 3px;
            border-radius: 4px;
            background: #5477DF;
            transform: translateX(-50%);
        }

        .nav-user,
        .user {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .user-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(145deg, #dce8ff, #c6d8ff);
            color: #6382d9;
            font-size: 18px;
            flex-shrink: 0;
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
            padding: 9px 20px;
            border: none;
            border-radius: 22px;
            background: linear-gradient(100deg, #f43d61, #fa5272);
            color: #ffffff;
            font-family: "DM Sans", sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(239, 65, 99, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(239, 65, 99, 0.30);
        }

        .logout-btn i { font-size: 14px; }

        @media (max-width: 700px) {
            .navbar-card {
                width: calc(100% - 40px);
                padding: 0 20px;
            }

            .nav-divider {
                margin: 0 15px;
            }
        }

        @media (max-width: 480px) {
            .navbar-card {
                width: calc(100% - 20px);
            }
        }
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
        <header class="navbar-card">
            <div class="nav-left">
                <a href="{{ route($dashboard) }}" class="navbar-logo">
                    @yield('logo_icon')
                    <span class="logo-word"><span class="logo-aula">Aula</span><span class="logo-virtual">Virtual</span></span>
                </a>
                <div class="nav-divider"></div>
                <ul class="nav-menu hidden md:flex">
                    <li>
                        <a href="{{ route($dashboard) }}" class="nav-link {{ request()->routeIs($dashboard) ? 'active' : '' }}">Dashboard</a>
                    </li>
                    @yield('menu_extra')
                </ul>
            </div>
            <div class="nav-right">
                <div class="user">
                    <div class="user-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span class="user-name hidden sm:inline">
                        {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}
                    </span>
                </div>
                <div class="nav-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Salir
                    </button>
                </form>
            </div>
        </header>
    @endauth

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('contenido')
    </main>
</body>
</html>