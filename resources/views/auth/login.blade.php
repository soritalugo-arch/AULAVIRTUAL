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

    <style>
        /* =====================================================
           CONFIGURACIÓN GENERAL
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;

            font-family: "DM Sans", sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #e8f0ff 0%,
                    #f5f8ff 48%,
                    #e9f1ff 100%
                );

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;
        }


        /* =====================================================
           FONDO DECORATIVO
        ===================================================== */

        .background-shape {
            position: fixed;
            z-index: 0;

            pointer-events: none;
        }


        /* Forma superior izquierda */

        .shape-top-left {
            width: 520px;
            height: 520px;

            top: -270px;
            left: -120px;

            background: rgba(164, 190, 239, 0.30);

            border-radius: 50%;
        }


        .shape-top-left::after {
            content: "";

            position: absolute;

            width: 420px;
            height: 420px;

            top: 130px;
            left: 100px;

            background: rgba(210, 224, 249, 0.75);

            border-radius: 50%;
        }


        /* Forma inferior derecha */

        .shape-bottom-right {
            width: 700px;
            height: 420px;

            right: -260px;
            bottom: -170px;

            background: rgba(174, 199, 241, 0.28);

            border-radius: 55% 0 0 0;
            transform: rotate(-17deg);
        }


        .shape-bottom-right::after {
            content: "";

            position: absolute;

            width: 560px;
            height: 280px;

            right: 70px;
            bottom: 100px;

            background: rgba(215, 227, 249, 0.65);

            border-radius: 60% 0 0 0;
        }


        /* =====================================================
           TARJETA PRINCIPAL
        ===================================================== */

        .login-card {
            position: relative;
            z-index: 2;

            width: min(76vw, 950px);
            min-height: 900px;

            padding: 48px 68px 70px;

            background: rgba(255, 255, 255, 0.86);

            border: 1px solid rgba(150, 180, 235, 0.38);

            border-radius: 36px;

            box-shadow:
                0 20px 55px rgba(82, 112, 175, 0.13),
                0 3px 12px rgba(120, 150, 210, 0.07);

            backdrop-filter: blur(12px);

            display: flex;
            flex-direction: column;
            align-items: center;
        }


        /* =====================================================
           ICONO DE GRADUACIÓN
        ===================================================== */

        .graduation-icon {
            width: 140px;
            height: 105px;

            margin-top: 0;
            margin-bottom: 8px;
        }


        .graduation-icon svg {
            width: 100%;
            height: 100%;
        }


        /* =====================================================
           TÍTULO
        ===================================================== */

        .title {
            margin-bottom: 52px;

            font-family: "Playfair Display", Georgia, serif;

            font-size: clamp(48px, 6vw, 76px);
            font-weight: 500;

            line-height: 1;

            letter-spacing: -2.5px;

            text-align: center;

            white-space: nowrap;
        }


        .title-aula {
            color: #21157c;
        }


        .title-virtual {
            color: #587be1;
        }


        /* =====================================================
           FORMULARIO
        ===================================================== */

        .login-form {
            width: 100%;
            max-width: 810px;

            display: flex;
            flex-direction: column;
        }


        /* =====================================================
           GRUPOS
        ===================================================== */

        .form-group {
            width: 100%;
        }


        .form-group + .form-group {
            margin-top: 43px;
        }


        /* =====================================================
           ERROR DE VALIDACIÓN
        ===================================================== */

        .error-banner {
            margin-top: 34px;

            padding: 18px 24px;

            background: rgba(254, 226, 226, 0.85);

            border: 1px solid rgba(248, 113, 113, 0.55);

            border-radius: 20px;

            color: #b91c1c;

            font-size: 19px;
            font-weight: 500;

            text-align: center;
        }


        /* =====================================================
           LABELS
        ===================================================== */

        .form-label {
            margin-bottom: 17px;

            display: flex;
            align-items: center;

            gap: 20px;

            color: #435b9f;

            font-size: 27px;
            font-weight: 600;

            line-height: 1;
        }


        .label-icon {
            width: 38px;
            height: 38px;

            flex-shrink: 0;

            color: #4c66aa;
        }


        .label-icon svg {
            width: 100%;
            height: 100%;
        }


        /* =====================================================
           INPUT
        ===================================================== */

        .input-wrapper {
            position: relative;

            width: 100%;
            height: 98px;
        }


        .input {
            width: 100%;
            height: 100%;

            padding: 0 78px 0 120px;

            border: 2px solid #b7cdfb;

            border-radius: 52px;

            outline: none;

            background: rgba(237, 243, 255, 0.88);

            color: #405a9c;

            font-family: "DM Sans", sans-serif;
            font-size: 25px;
            font-weight: 400;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .input:focus {
            border-color: #6d8fe8;

            background: #f1f5ff;

            box-shadow:
                0 0 0 4px rgba(100, 137, 230, 0.10);
        }


        .input::placeholder {
            color: #405a9c;
            opacity: 1;
        }


        /* =====================================================
           ICONO DENTRO DEL INPUT
        ===================================================== */

        .input-icon {
            position: absolute;

            left: 43px;
            top: 50%;

            width: 38px;
            height: 38px;

            transform: translateY(-50%);

            color: #526daf;

            pointer-events: none;
        }


        .input-icon svg {
            width: 100%;
            height: 100%;
        }


        /* =====================================================
           BOTÓN MOSTRAR CONTRASEÑA
        ===================================================== */

        .password-toggle {
            position: absolute;

            right: 30px;
            top: 50%;

            width: 50px;
            height: 50px;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #5b73b0;

            cursor: pointer;

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .password-toggle svg {
            width: 39px;
            height: 39px;
        }


        .password-toggle:hover {
            color: #405da5;
        }


        /* =====================================================
           BOTÓN INICIAR SESIÓN
        ===================================================== */

        .login-button {
            width: 100%;
            height: 108px;

            margin-top: 57px;

            border: none;

            border-radius: 56px;

            background:
                linear-gradient(
                    100deg,
                    #4b4eb8 0%,
                    #596bd0 40%,
                    #709dff 100%
                );

            color: white;

            font-family: "DM Sans", sans-serif;

            font-size: 29px;
            font-weight: 600;

            cursor: pointer;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 24px;

            box-shadow:
                0 8px 20px rgba(83, 104, 202, 0.14);

            transition:
                transform 0.18s ease,
                box-shadow 0.18s ease,
                filter 0.18s ease;
        }


        .login-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(83, 104, 202, 0.20);

            filter: brightness(1.03);
        }


        .login-button:active {
            transform: translateY(0);
        }


        .login-button svg {
            width: 49px;
            height: 49px;

            flex-shrink: 0;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .login-card {
                width: 90vw;
                min-height: auto;

                padding: 45px 40px 55px;
            }

            .title {
                font-size: 58px;
            }

            .login-form {
                max-width: 100%;
            }
        }


        @media (max-width: 600px) {

            body {
                overflow-y: auto;
                padding: 25px 0;
            }

            .login-card {
                width: calc(100vw - 30px);

                padding: 35px 22px 45px;

                border-radius: 28px;
            }

            .graduation-icon {
                width: 105px;
                height: 80px;
            }

            .title {
                margin-bottom: 38px;

                font-size: 42px;
                letter-spacing: -1.5px;
            }

            .form-label {
                font-size: 21px;
                gap: 13px;
            }

            .label-icon {
                width: 30px;
                height: 30px;
            }

            .input-wrapper {
                height: 75px;
            }

            .input {
                padding-left: 80px;
                padding-right: 60px;

                font-size: 17px;
            }

            .input-icon {
                left: 26px;

                width: 29px;
                height: 29px;
            }

            .password-toggle {
                right: 17px;
            }

            .login-button {
                height: 82px;

                margin-top: 43px;

                font-size: 21px;
            }

            .login-button svg {
                width: 39px;
                height: 39px;
            }
        }
    </style>
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

                    <span class="label-icon">

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

                    <span class="label-icon">

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


    <!-- =====================================================
         JAVASCRIPT (solo mostrar/ocultar contraseña)
    ====================================================== -->

    <script>

        const passwordInput =
            document.getElementById("password");

        const togglePassword =
            document.getElementById("togglePassword");

        const eyeIcon =
            document.getElementById("eyeIcon");


        togglePassword.addEventListener("click", () => {

            const isPassword =
                passwordInput.type === "password";


            passwordInput.type =
                isPassword ? "text" : "password";


            togglePassword.setAttribute(
                "aria-label",
                isPassword
                    ? "Ocultar contraseña"
                    : "Mostrar contraseña"
            );


            if (isPassword) {

                eyeIcon.innerHTML = `
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
                `;

            } else {

                eyeIcon.innerHTML = `
                    <path
                        d="M3 20
                           C7 13
                           13 9
                           20 9
                           C27 9
                           33 13
                           37 20"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />

                    <path
                        d="M37 20
                           C33 27
                           27 31
                           20 31
                           C13 31
                           7 27
                           3 20"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />

                    <path
                        d="M7 7L33 33"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />
                `;
            }

        });

    </script>

</body>
</html>