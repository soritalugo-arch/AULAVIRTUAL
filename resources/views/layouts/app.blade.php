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
            position: relative;
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

        /* Indicador deslizante bajo el enlace activo */
        .nav-indicator {
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 0;
            height: 3px;
            border-radius: 4px;
            background: #5477DF;
            opacity: 0;
            transform: translateX(0px);
            pointer-events: none;
            transition:
                transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
                <ul class="nav-menu hidden md:flex">
                    <li class="nav-indicator" aria-hidden="true"></li>
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

    @stack('scripts')

    {{-- Indicador deslizante del navbar: anima de un enlace a otro entre páginas --}}
    <script>
        (function () {
            var menu = document.querySelector('.nav-menu');
            var indicator = document.querySelector('.nav-indicator');
            if (!menu || !indicator) return;

            var links = Array.prototype.slice.call(menu.querySelectorAll('a.nav-link'));
            if (!links.length) return;

            var active = null;
            for (var i = 0; i < links.length; i++) {
                if (links[i].classList.contains('active')) { active = links[i]; break; }
            }
            var activeIndex = active ? links.indexOf(active) : -1;
            var firstHref = links[0] ? links[0].getAttribute('href') : null;

            function visible(el) { return el.offsetParent !== null; }

            function pos(el) {
                var m = menu.getBoundingClientRect();
                var l = el.getBoundingClientRect();
                return { left: l.left - m.left + (menu.scrollLeft || 0), width: l.width };
            }

            function place(x, w, animate) {
                indicator.style.transition = animate ? '' : 'none';
                indicator.style.opacity = '1';
                indicator.style.transform = 'translateX(' + x + 'px)';
                indicator.style.width = w + 'px';
            }

            function render(animate) {
                if (!active || !visible(menu)) {
                    indicator.style.opacity = '0';
                    return;
                }
                var p = pos(active);
                place(p.left, p.width, animate);
            }

            var prev = null;
            try { prev = JSON.parse(sessionStorage.getItem('__aulaNavIndicator') || 'null'); } catch (e) {}

            var coinciden = prev && prev.index >= 0 && prev.index < links.length
                         && prev.index !== activeIndex && prev.firstHref === firstHref;

            if (active && coinciden) {
                // Parte desde la posición del enlace anterior y desliza al actual
                var from = pos(links[prev.index]);
                place(from.left, from.width, false);
                void indicator.offsetWidth; // fuerza reflow antes de animar
                render(true);
            } else {
                render(false); // primera visita o menú distinto: aparece sin animación
            }

            try {
                sessionStorage.setItem('__aulaNavIndicator',
                    JSON.stringify({ index: activeIndex, firstHref: firstHref }));
            } catch (e) {}

            window.addEventListener('resize', function () { render(false); });
        })();
    </script>
</body>
</html>