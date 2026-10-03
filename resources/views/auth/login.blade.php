<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AulaVirtual - Iniciar sesión</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">


    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/auth/login.css')
    @endif
</head>


<body>

    <!-- =====================================================
         DECORACIÓN DEL FONDO
    ====================================================== -->

    <div class="background-shape shape-top-left"></div>
    <div class="background-shape shape-bottom-right"></div>


    <!-- =====================================================
         TARJETA DE LOGIN
    ====================================================== -->

    <main class="login-card">


        <!-- =================================================
             BIRRETE DE GRADUACIÓN
        ================================================== -->

        <div class="graduation-icon">

            <svg viewBox="0 0 140 105"
                 xmlns="http://www.w3.org/2000/svg"
                 aria-hidden="true">

                <defs>
                    <linearGradient id="graduationGradient"
                                    x1="0"
                                    y1="0"
                                    x2="1"
                                    y2="1">

                        <stop offset="0%" stop-color="#4c5bc3"/>
                        <stop offset="100%" stop-color="#6e94ee"/>

                    </linearGradient>
                </defs>


                <!-- Parte superior del birrete -->

                <polygon
                    points="70,4 136,34 70,65 4,34"
                    fill="url(#graduationGradient)"
                />


                <!-- Pequeño punto central -->

                <circle
                    cx="70"
                    cy="34"
                    r="3"
                    fill="white"
                />


                <!-- Parte inferior -->

                <path
                    d="M26 49
                       L26 72
                       Q26 78 32 81
                       L64 96
                       Q70 99 76 96
                       L108 81
                       Q114 78 114 72
                       L114 49
                       L70 69
                       Z"
                    fill="url(#graduationGradient)"
                />


                <!-- Línea del birrete -->

                <path
                    d="M26 49
                       L70 70
                       L114 49"
                    fill="none"
                    stroke="#ffffff"
                    stroke-width="4"
                    opacity="0.9"
                />


                <!-- Cordón -->

                <path
                    d="M125 35
                       L125 61"
                    fill="none"
                    stroke="#5a73d2"
                    stroke-width="4"
                    stroke-linecap="round"
                />

                <circle
                    cx="125"
                    cy="67"
                    r="7"
                    fill="#607ddc"
                />

                <path
                    d="M125 73 L125 91"
                    fill="none"
                    stroke="#607ddc"
                    stroke-width="4"
                    stroke-linecap="round"
                />

            </svg>

        </div>


        <!-- =================================================
             TÍTULO
        ================================================== -->

        <h1 class="title">
            <span class="title-aula">Aula</span><span class="title-virtual">Virtual</span>
        </h1>


        <!-- =================================================
             FORMULARIO
        ================================================== -->

        <form class="login-form" method="POST" action="{{ route('login.submit') }}">

            @csrf

            @if ($errors->any())
                <div class="error-banner" id="error">{{ $errors->first('email') }}</div>
            @endif


            <!-- =============================================
                 EMAIL
            ============================================== -->

            <div class="form-group">

                <label class="form-label" for="email">

                    <span>Email</span>

                </label>


                <div class="input-wrapper">

                    <span class="input-icon">

                        <svg viewBox="0 0 40 40"
                             fill="none"
                             xmlns="http://www.w3.org/2000/svg">

                            <rect
                                x="4"
                                y="8"
                                width="32"
                                height="24"
                                rx="3"
                                stroke="currentColor"
                                stroke-width="2.5"
                            />

                            <path
                                d="M5 11L20 23L35 11"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                        </svg>

                    </span>


                    <input
                        class="input"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="nombre.apellido-rol@aula.edu"
                        autocomplete="email"
                        required
                        autofocus
                    />

                </div>

            </div>


            <!-- =============================================
                 CONTRASEÑA
            ============================================== -->

            <div class="form-group">

                <label class="form-label" for="password">

                    <span>Contraseña</span>

                </label>


                <div class="input-wrapper">

                    <span class="input-icon">

                        <svg viewBox="0 0 40 40"
                             fill="none"
                             xmlns="http://www.w3.org/2000/svg">

                            <rect
                                x="9"
                                y="17"
                                width="22"
                                height="18"
                                rx="3"
                                stroke="currentColor"
                                stroke-width="2.5"
                            />

                            <path
                                d="M13 17V12
                                   C13 7.5 16 5 20 5
                                   C24 5 27 7.5 27 12V17"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            />

                            <circle
                                cx="20"
                                cy="25"
                                r="2"
                                fill="currentColor"
                            />

                            <path
                                d="M20 27V30"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            />

                        </svg>

                    </span>


                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    />


                    <!-- Botón mostrar contraseña -->

                    <button
                        type="button"
                        class="password-toggle"
                        id="togglePassword"
                        aria-label="Mostrar contraseña">

                        <svg
                            id="eyeIcon"
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg">

                            <path
                                d="M3 20
                                   C7 13
                                   13 9
                                   20 9
                                   C27 9
                                   33 13
                                   37 20
                                   C33 27
                                   27 31
                                   20 31
                                   C13 31
                                   7 27
                                   3 20Z"
                                stroke="currentColor"
                                stroke-width="2.5"
                            />

                            <circle
                                cx="20"
                                cy="20"
                                r="5"
                                stroke="currentColor"
                                stroke-width="2.5"
                            />

                        </svg>

                    </button>

                </div>

            </div>


            <!-- =============================================
                 BOTÓN LOGIN
            ============================================== -->

            <button
                type="submit"
                class="login-button">

                <!-- Icono entrar -->

                <svg
                    viewBox="0 0 50 50"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">

                    <path
                        d="M28 10H38
                           C40.8 10 43 12.2 43 15V35
                           C43 37.8 40.8 40 38 40H28"
                        stroke="white"
                        stroke-width="3"
                        stroke-linecap="round"
                    />

                    <path
                        d="M7 25H31"
                        stroke="white"
                        stroke-width="3"
                        stroke-linecap="round"
                    />

                    <path
                        d="M24 18L31 25L24 32"
                        stroke="white"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                </svg>


                <span>Iniciar sesión</span>

            </button>

        </form>

    </main>


    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/js/auth/login.js')
    @endif

</body>
</html>