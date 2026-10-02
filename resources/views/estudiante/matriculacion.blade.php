@extends('layouts.app')

@section('titulo', 'Matriculación de Asignaturas')

@section('menu_extra')
    <li>
        <a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a>
    </li>
    <li>
        <a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a>
    </li>
    <li>
        <a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a>
    </li>
    <li>
        <a href="{{ route('estudiante.plan') }}" class="nav-link {{ request()->routeIs('estudiante.plan') ? 'active' : '' }}">Plan de Estudios</a>
    </li>
@endsection

@section('contenido')

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    /* ================================
       CONFIGURACIÓN GENERAL
    ================================ */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background:
            radial-gradient(
                circle at 10% 10%,
                rgba(194, 216, 255, 0.45),
                transparent 35%
            ),
            radial-gradient(
                circle at 90% 90%,
                rgba(188, 211, 255, 0.5),
                transparent 35%
            ),
            #f3f6fb;

        color: #172b5c;
        min-height: 100vh;
    }


    /* ================================
       CONTENEDOR PRINCIPAL
    ================================ */

    /* El main del layout no agrega padding propio en esta pagina */
    main {
        padding-left: 0;
        padding-right: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    /* Margen lateral amplio (~45px por lado), igual que Mis Notas */
    .matriculacion-wrap {
        width: calc(100% - 90px);

        margin: 35px auto 50px;
    }


    /* ================================
       MENSAJES DE ESTADO
    ================================ */

    .alert-banner {
        margin-bottom: 18px;

        padding: 16px 24px;

        border-radius: 18px;

        font-size: 15px;
        font-weight: 500;
    }

    .alert-success {
        background: rgba(220, 252, 231, 0.95);

        border: 1px solid #86efac;

        color: #166534;
    }

    .alert-info {
        background: rgba(219, 234, 254, 0.95);

        border: 1px solid #93c5fd;

        color: #1e40af;
    }

    .alert-error {
        background: rgba(254, 226, 226, 0.95);

        border: 1px solid #fca5a5;

        color: #991b1b;
    }

    .alert-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 14px;
    }

    .alert-msg {
        display: flex;
        align-items: center;

        gap: 10px;
    }

    .alert-close {
        flex-shrink: 0;

        border: none;

        background: transparent;

        color: inherit;

        font-size: 22px;

        line-height: 1;

        cursor: pointer;

        opacity: 0.55;

        transition: opacity 0.15s ease;
    }

    .alert-close:hover {
        opacity: 1;
    }


    /* ================================
       ALERTA DE DEUDA
    ================================ */

    .deuda-alert {
        margin-bottom: 18px;

        padding: 18px 24px;

        display: flex;
        align-items: flex-start;

        gap: 14px;

        background: linear-gradient(110deg, #fef9c3, #fef3c7);

        border: 1px solid #fde047;

        border-radius: 18px;

        color: #854d0e;
    }

    .deuda-alert i {
        font-size: 24px;

        color: #eab308;

        margin-top: 2px;
    }

    .deuda-alert h3 {
        font-size: 16px;

        font-weight: 700;

        margin-bottom: 4px;
    }

    .deuda-alert p {
        font-size: 14px;

        line-height: 1.5;
    }


    /* ================================
       TARJETA DEL MÓDULO
    ================================ */

    .module-card {
        position: relative;

        min-height: 185px;

        padding: 30px;

        display: flex;
        align-items: center;

        gap: 28px;

        overflow: hidden;

        background:
            linear-gradient(
                110deg,
                rgba(255, 255, 255, 0.98),
                rgba(246, 249, 255, 0.96)
            );

        border: 1px solid #d9e6fb;

        border-radius: 27px;

        box-shadow:
            0 9px 25px rgba(76, 109, 169, 0.09);
    }


    /* Decoración de fondo */

    .module-card::after {
        content: "";

        position: absolute;

        width: 470px;
        height: 230px;

        right: -100px;
        bottom: -130px;

        background: rgba(184, 207, 252, 0.32);

        border-radius: 50%;

        transform: rotate(-18deg);
    }


    /* ================================
       ICONO DEL MÓDULO
    ================================ */

    .module-icon {
        position: relative;
        z-index: 2;

        width: 125px;
        height: 125px;

        flex-shrink: 0;

        border-radius: 50%;

        background:
            linear-gradient(
                145deg,
                #dce8ff,
                #edf3ff
            );

        display: flex;
        align-items: center;
        justify-content: center;

        color: #6685df;

        font-size: 53px;
    }

    .module-icon svg {
        width: 112px;
        height: 112px;
    }

    /* Icono: matrícula con documento, lista y usuario */

    .module-icon .icon-background {

        fill: #e1eaff;
    }

    .module-icon .document {

        fill: #6b82e5;
    }

    .module-icon .document-fold {

        fill: #526bd2;
    }

    .module-icon .document-line {

        fill: #eef3ff;
    }

    .module-icon .document-dot {

        fill: #eef3ff;
    }

    .module-icon .user {

        fill: #5c73d7;
    }

    .module-icon .user-outline {

        fill: #eef3ff;
    }

    .module-icon .add-circle {

        fill: #526bd2;
    }

    .module-icon .add-symbol {

        fill: #f4f7ff;
    }


    /* ================================
       INFORMACIÓN DEL MÓDULO
    ================================ */

    .module-info {
        position: relative;
        z-index: 2;
    }

    .module-info h1 {
        font-family: Georgia, "Times New Roman", serif;

        font-size: 35px;

        color: #171c7c;

        margin-bottom: 7px;
    }

    .module-description {
        font-size: 17px;

        color: #7890c2;

        margin-bottom: 16px;
    }


    /* ================================
       DATOS
    ================================ */

    .module-data {
        display: flex;
        flex-direction: column;

        gap: 8px;
    }

    .module-data div {
        display: flex;
        align-items: center;

        gap: 8px;

        font-size: 15px;

        color: #7186b5;
    }

    .module-data i {
        width: 19px;

        color: #5e7edc;
    }

    .module-data strong {
        color: #4662a3;
    }


    /* ================================
       TARJETA ACADÉMICA
    ================================ */

    .academic-card {
        margin-top: 25px;

        padding: 24px 27px 27px;

        background: rgba(255, 255, 255, 0.93);

        border: 1px solid #e0e8f5;

        border-radius: 27px;

        box-shadow:
            0 8px 25px rgba(70, 100, 160, 0.08);
    }


    /* ================================
       TÍTULO
    ================================ */

    .academic-title {
        display: flex;
        align-items: center;

        gap: 15px;

        margin-bottom: 20px;
    }

    .academic-title i {
        font-size: 28px;

        color: #6382dc;
    }

    .academic-title svg {
        width: 30px;
        height: 30px;

        color: #6382dc;

        flex-shrink: 0;
    }

    .academic-title h2 {
        font-family: Georgia, "Times New Roman", serif;

        font-size: 29px;

        color: #171c7c;
    }


    /* ================================
       CABECERA DE TABLA
    ================================ */

    .table-header {
        height: 47px;

        padding: 0 27px;

        display: grid;

        grid-template-columns:
            42%
            43%
            15%;

        align-items: center;

        color: #6e83b3;

        font-size: 13px;

        font-weight: 600;

        letter-spacing: 0.5px;

        background: #f1f6ff;

        border: 1px solid #dbe8fb;

        border-radius: 20px 20px 0 0;
    }


    /* ================================
       FILAS
    ================================ */

    .subject-row {
        min-height: 65px;

        padding: 7px 27px;

        display: grid;

        grid-template-columns:
            42%
            43%
            15%;

        align-items: center;

        border: 1px solid #dce8fb;

        border-top: none;

        background: rgba(255, 255, 255, 0.75);

        transition: 0.2s ease;
    }

    .subject-row:last-child {
        border-radius: 0 0 18px 18px;
    }

    .subject-row:hover {
        background: #f8faff;
    }


    /* ================================
       ASIGNATURA
    ================================ */

    .subject {
        display: flex;

        align-items: center;

        gap: 23px;

        color: #223d91;

        font-size: 15px;

        font-weight: 600;
    }

    .subject-icon {
        width: 42px;
        height: 42px;

        border-radius: 13px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        background:
            linear-gradient(
                145deg,
                #e1ebff,
                #d2e0ff
            );

        color: #6080db;

        font-size: 17px;
    }


    /* ================================
       CUPOS
    ================================ */

    .spaces {
        display: flex;

        align-items: center;

        gap: 12px;

        color: #7187b8;

        font-size: 15px;
    }


    /* ESTADO LLENO */

    .full {
        padding: 5px 12px;

        border-radius: 20px;

        background: #ffe5eb;

        color: #ed6d87;

        font-size: 12px;

        font-weight: 700;
    }


    /* ================================
       ACCIONES
    ================================ */

    .action {
        display: flex;

        justify-content: flex-end;

        align-items: center;

        gap: 10px;
    }


    /* LISTA DE ESPERA */

    .waiting {
        color: #4a618f;

        font-size: 13px;

        font-weight: 600;

        white-space: nowrap;
    }


    /* ================================
       BOTONES
    ================================ */

    button {
        border: none;

        font-family: inherit;

        cursor: pointer;
    }

    .btn-enroll {
        min-width: 150px;

        height: 38px;

        padding: 0 17px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        border-radius: 20px;

        background:
            linear-gradient(
                100deg,
                #5862e5,
                #668cf0
            );

        color: white;

        font-weight: 700;

        font-size: 13px;

        box-shadow:
            0 5px 12px rgba(91, 111, 224, 0.25);

        transition: 0.2s ease;
    }

    .btn-enroll:hover {
        transform: translateY(-1px);

        box-shadow:
            0 8px 17px rgba(91, 111, 224, 0.3);
    }

    .btn-enroll i {
        margin-right: 0;
    }


    .btn-remove {
        min-width: 150px;

        height: 38px;

        padding: 0 17px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        border-radius: 20px;

        background:
            linear-gradient(
                100deg,
                #f03458,
                #f8486b
            );

        color: white;

        font-weight: 700;

        font-size: 13px;

        box-shadow:
            0 5px 12px rgba(239, 58, 91, 0.20);

        transition: 0.2s ease;
    }

    .btn-remove:hover {
        transform: translateY(-1px);

        box-shadow:
            0 8px 17px rgba(239, 58, 91, 0.28);
    }

    .btn-remove i {
        margin-right: 0;
    }


    /* Botón secundario "Salir de lista" */

    .btn-exit-list {
        min-width: 150px;

        height: 38px;

        padding: 0 14px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        border-radius: 20px;

        border: 1px solid #d3dff2;

        background: #ffffff;

        color: #4a618f;

        font-family: inherit;

        font-weight: 600;

        font-size: 13px;

        white-space: nowrap;

        transition: 0.2s ease;
    }

    .btn-exit-list:hover {
        background: #f2f6ff;
    }

    .btn-exit-list i {
        margin-right: 0;
    }


    .btn-enroll:disabled,
    .btn-remove:disabled {
        opacity: 0.45;

        cursor: not-allowed;

        transform: none;

        box-shadow: none;
    }


    /* ================================
       DIÁLOGO DE CONFIRMACIÓN
    ================================ */

    .confirm-dialog {
        position: fixed;
        inset: 0;

        margin: auto;

        border: none;

        border-radius: 24px;

        padding: 32px 34px;

        width: min(460px, 92vw);

        background: linear-gradient(110deg, #ffffff, #fbfdff);

        box-shadow: 0 20px 45px rgba(40, 66, 120, 0.28);

        text-align: center;

        font-family: "DM Sans", Arial, sans-serif;

        color: #172b5c;
    }

    .confirm-dialog::backdrop {
        background: rgba(23, 27, 66, 0.45);

        backdrop-filter: blur(2px);
    }

    .confirm-icon {
        width: 62px;
        height: 62px;

        margin: 0 auto 18px;

        border-radius: 50%;

        background: linear-gradient(145deg, #ffe3ea, #ffd3dc);

        display: flex;
        align-items: center;
        justify-content: center;

        color: #f03458;

        font-size: 26px;
    }

    .confirm-dialog h3 {
        font-family: Georgia, "Times New Roman", serif;

        font-size: 24px;

        color: #171c7c;

        margin-bottom: 10px;
    }

    .confirm-msg {
        font-size: 15px;

        color: #7186b5;

        line-height: 1.5;

        margin-bottom: 6px;
    }

    .confirm-curso {
        display: block;

        font-weight: 700;

        font-size: 15px;

        color: #223d91;

        margin-bottom: 24px;
    }

    .confirm-actions {
        display: flex;
        justify-content: center;

        gap: 14px;
    }


    /* ================================
       FILA VACÍA
    ================================ */

    .subject-empty {
        display: flex;
        justify-content: center;

        color: #7187b8;

        font-size: 15px;

        padding: 28px 27px;
    }


/* ================================================
       RESPONSIVE (REEMPLAZO COMPLETO DE @MEDIA)
    ================================================ */

    @media (max-width: 900px) {
        .module-card {
            padding: 22px;
            align-items: center;
        }

        .module-icon {
            width: 80px;
            height: 80px;
            font-size: 34px;
        }

        .module-icon svg {
            width: 70px;
            height: 70px;
        }

        .module-info h1 {
            font-size: 26px;
        }
    }

    @media (max-width: 768px) {
        /* 0. Seguro anti-desbordamiento global */
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
        }

        /* 1. Ajuste del contenedor principal (usamos auto en vez de 100% para evitar sumar márgenes) */
        .matriculacion-wrap {
            width: auto !important;
            margin: 20px 15px 35px 15px !important; 
            padding: 0 !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }

        /* 2. Tarjeta superior del módulo */
        .module-card {
            width: auto !important;
            flex-direction: column;
            text-align: center;
            padding: 20px 15px !important;
            gap: 15px;
            border-radius: 20px !important;
            box-sizing: border-box !important;
        }

        .module-card::after {
            display: none !important;
        }

        .module-data {
            align-items: center;
            width: 100%;
        }

        .module-info h1 {
            font-size: 22px !important; 
            word-break: break-word !important;
        }

        .module-description {
            font-size: 14px !important;
        }

        /* 3. Sección de la Oferta Académica */
        .academic-card {
            width: auto !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            margin-top: 20px !important;
            box-sizing: border-box !important;
        }

        .academic-title {
            justify-content: center;
            margin-bottom: 15px;
            text-align: center;
            flex-wrap: wrap !important; /* Evita que el icono y texto se empujen */
        }

        .academic-title h2 {
            font-size: 20px !important;
            white-space: normal !important;
        }

        .table-header {
            display: none !important;
        }

        /* 4. Transformación de filas */
        .subject-row {
            display: flex !important;
            flex-direction: column !important;
            width: auto !important; 
            margin: 0 0 15px 0 !important;
            padding: 16px 15px !important;
            background: #ffffff !important;
            border: 1px solid #dce8fb !important;
            border-radius: 18px !important;
            box-shadow: 0 4px 10px rgba(70, 100, 160, 0.08) !important;
            box-sizing: border-box !important;
        }

        /* 5. Fila 1 y 2: Asignatura y Cupos */
        .subject, .spaces {
            display: flex !important;
            flex-wrap: wrap !important; /* Si el contenido es muy largo, bajará a otra línea */
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;       
            gap: 10px !important;
            padding-bottom: 10px !important;
            margin-bottom: 10px !important;
            border-bottom: 1px solid #edf0f5 !important;
            box-sizing: border-box !important;
        }

        .subject::before {
            content: "ASIGNATURA";
            font-size: 11px !important;
            font-weight: 800 !important;
            color: #6b82b5 !important;
            letter-spacing: 0.5px !important;
        }

        .spaces::before {
            content: "CUPOS";
            font-size: 11px !important;
            font-weight: 800 !important;
            color: #6b82b5 !important;
            letter-spacing: 0.5px !important;
        }

        .subject > span, .spaces > span:first-of-type {
            flex: 1 1 auto !important;
            min-width: 0 !important;      
            text-align: right !important; 
            font-size: 13px !important;   
            color: #223d91 !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
            word-break: break-word !important; /* Corta la palabra estrictamente si no cabe */
        }

        .spaces > span:first-of-type {
            font-weight: 600 !important;
        }

        .subject-icon {
            display: none !important; 
        }

        /* 6. Fila 3: Botones de Acción */
        .action {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;       
            justify-content: center !important;
            align-items: stretch !important;
            gap: 8px !important;
            box-sizing: border-box !important;
        }

        .action form {
            width: 100% !important;
            display: block !important;
        }

        .action .btn-enroll,
        .action .btn-remove,
        .action .btn-exit-list {
            width: 100% !important;
            min-width: 0 !important;
            height: 42px !important;
            font-size: 13px !important;
            border-radius: 12px !important;
            justify-content: center !important;
            box-sizing: border-box !important;
        }

        .action .waiting {
            text-align: center !important;
            width: 100% !important;
            font-size: 12px !important;
        }
    }
</style>

<div class="matriculacion-wrap">

    <!-- Mensajes de estado -->
    @if(session('success'))
    <div class="alert-banner alert-success" role="alert">
        <span class="alert-msg"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</span>
        <button type="button" class="alert-close" aria-label="Cerrar aviso">&times;</button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert-banner alert-info" role="alert">
        <span class="alert-msg"><i class="fa-solid fa-circle-info"></i> {{ session('info') }}</span>
        <button type="button" class="alert-close" aria-label="Cerrar aviso">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert-banner alert-error" role="alert">
        <span class="alert-msg"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</span>
        <button type="button" class="alert-close" aria-label="Cerrar aviso">&times;</button>
    </div>
    @endif

    <!-- Alerta de deuda -->
    @if($estudiante->deuda)
    <div class="deuda-alert" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
            <h3>Atención</h3>
            <p>Posees deudas pendientes en el sistema. No podrás inscribir nuevas asignaturas hasta solventar tu estado de cuenta.</p>
        </div>
    </div>
    @endif

    @php
        // Momento del período: solo en "matrícula" se puede inscribir.
        $matriculaAbierta = $cuatrimestreVigente?->estado === 'matriculacion';

        // Iconos por materia (solo visual)
        $mapaIconos = [
            'programación' => 'fa-code',
            'base de datos' => 'fa-database',
            'base' => 'fa-database',
            'matemá' => 'fa-calculator',
            'cálculo' => 'fa-calculator',
            'diseño' => 'fa-pen-ruler',
            'marketing' => 'fa-bullhorn',
            'turismo' => 'fa-plane',
            'enfermería' => 'fa-user-nurse',
            'electrónica' => 'fa-microchip',
            'instalaciones' => 'fa-bolt',
            'inglés' => 'fa-language',
            'emprendimiento' => 'fa-rocket',
            'anatomía' => 'fa-heart-pulse',
            'contabilidad' => 'fa-coins',
            'ofimática' => 'fa-file-lines',
            'redes' => 'fa-globe',
            'sistema' => 'fa-gear',
            'expresión' => 'fa-comment-dots',
            'física' => 'fa-atom',
            'química' => 'fa-flask',
            'economía' => 'fa-chart-line',
        ];

        $iconoCurso = function (string $nombre) use ($mapaIconos): string {
            $nombre = mb_strtolower($nombre);

            foreach ($mapaIconos as $clave => $icono) {
                if (mb_strpos($nombre, $clave) !== false) {
                    return $icono;
                }
            }

            return 'fa-book-open';
        };
    @endphp

    <!-- INFORMACIÓN DEL MÓDULO -->
    <section class="module-card">

        <div class="module-icon">
            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">

                <!-- Fondo circular -->
                <circle class="icon-background" cx="512" cy="512" r="465" />

                <!-- Documento -->
                <path class="document"
                      d="M293 272 C293 250 311 232 333 232 H560 L672 344 V690 C672 713 654 731 631 731 H333 C311 731 293 713 293 690 Z" />

                <!-- Esquina doblada -->
                <path class="document-fold"
                      d="M560 232 V319 C560 337 575 352 593 352 H672 Z" />

                <!-- Línea superior del documento -->
                <rect class="document-line" x="360" y="335" width="225" height="38" rx="19" />

                <!-- Primer punto + línea -->
                <circle class="document-dot" cx="377" cy="431" r="16" />
                <rect class="document-line" x="420" y="414" width="184" height="34" rx="17" />

                <!-- Segundo punto + línea -->
                <circle class="document-dot" cx="377" cy="502" r="16" />
                <rect class="document-line" x="420" y="485" width="130" height="34" rx="17" />

                <!-- Tercer punto + línea -->
                <circle class="document-dot" cx="377" cy="573" r="16" />
                <rect class="document-line" x="420" y="556" width="120" height="34" rx="17" />

                <!-- Contorno blanco del usuario: cabeza + cuerpo -->
                <circle class="user-outline" cx="653" cy="550" r="76" />
                <path class="user-outline" d="M535 756 C535 684 588 638 653 638 C718 638 771 684 771 756 Z" />

                <!-- Usuario: cabeza + cuerpo -->
                <circle class="user" cx="653" cy="550" r="57" />
                <path class="user" d="M555 756 C555 696 598 657 653 657 C708 657 751 696 751 756 Z" />

                <!-- Círculo de agregar -->
                <circle class="add-circle" cx="770" cy="718" r="76" />

                <!-- Signo "+" -->
                <rect class="add-symbol" x="755" y="675" width="30" height="86" rx="15" />
                <rect class="add-symbol" x="727" y="703" width="86" height="30" rx="15" />

            </svg>
        </div>

        <div class="module-info">

            <h1>Módulo de Matriculación</h1>

            <p class="module-description">
                Gestiona la inscripción y retiro de tus asignaturas.
            </p>

            <div class="module-data">

                <div>
                    <i class="fa-regular fa-calendar"></i>
                    <span>Carrera:</span>
                    <strong>{{ $estudiante->carrera ? $estudiante->carrera->nombre : '—' }}</strong>
                </div>

                <div>
                    <i class="fa-regular fa-calendar"></i>
                    <span>Tu cuatrimestre del plan:</span>
                    <strong>
                        {{ $etapaActual
                            ? $etapaActual . ' de ' . ($totalEtapas ?: $etapaActual)
                            : 'Completaste tu carrera' }}
                    </strong>
                </div>

                <div>
                    <i class="fa-regular fa-calendar"></i>
                    <span>Período vigente:</span>
                    <strong>
                        {{ $cuatrimestreVigente
                            ? $cuatrimestreVigente->fecha_inicio->format('d/m/Y') . ' al ' . $cuatrimestreVigente->fecha_fin->format('d/m/Y')
                            : '—' }}
                    </strong>
                </div>

            </div>

        </div>

    </section>


    <!-- AVISO DEL MOMENTO DEL PERÍODO -->
    @if(! $matriculaAbierta)
    <div class="alert-banner alert-info" role="alert">
        <span class="alert-msg">
            <i class="fa-solid fa-circle-info"></i>
            @if($cuatrimestreVigente?->estado === 'en_curso')
                La matrícula está cerrada: este cuatrimestre ya está en cursado.
            @else
                La matrícula está cerrada: este cuatrimestre ya finalizó.
            @endif
        </span>
    </div>
    @endif


    <!-- OFERTA ACADÉMICA -->
    <section class="academic-card">

        <div class="academic-title">
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M20 12C15.5 8.5 10 8 4 9V30C10 29 15.5 29.5 20 32C24.5 29.5 30 29 36 30V9C30 8 24.5 8.5 20 12Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" />
                <path d="M20 12V32" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            </svg>
            <h2>Oferta Académica</h2>
        </div>

        <!-- CABECERA -->
        <div class="table-header">

            <div>ASIGNATURA</div>
            <div>CUPOS</div>
            <div>ACCIÓN</div>

        </div>

        @forelse($cursos as $curso)

            @php
                $inscritos = $curso->inscripciones_count;
                $lleno     = $inscritos >= $curso->limite_estudiantes;
                $enEspera  = in_array($curso->id_curso, $misListaEsperaIds);
                $inscrito  = in_array($curso->id_curso, $misInscripcionesIds);
                $icono     = $iconoCurso($curso->nombre);
            @endphp

            <div class="subject-row">

                <div class="subject">

                    <div class="subject-icon">
                        <i class="fa-solid {{ $icono }}"></i>
                    </div>

                    <span>{{ $curso->nombre }}</span>

                </div>

                <div class="spaces">
                    <span>{{ $inscritos }} / {{ $curso->limite_estudiantes }} estudiantes</span>
                    @if($lleno)
                    <span class="full">Lleno</span>
                    @endif
                </div>

                <div class="action">

                    @if(! $matriculaAbierta)

                        <span class="waiting">Matrícula cerrada para este período</span>

                    @elseif($enEspera)

                        <span class="waiting">En lista de espera</span>

                        <form action="{{ route('estudiante.quitarListaEspera') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
                            <button type="submit" class="btn-exit-list">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                Salir de lista
                            </button>
                        </form>

                    @elseif($inscrito)

                        <form action="{{ route('estudiante.desinscribir') }}" method="POST" id="form-desinscribir-{{ $curso->id_curso }}">
                            @csrf
                            <input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
                            <button type="submit" class="btn-remove" data-submit-form="form-desinscribir-{{ $curso->id_curso }}" data-curso="{{ $curso->nombre }}">
                                <i class="fa-solid fa-circle-minus"></i>
                                Desmatricular
                            </button>
                        </form>

                    @else

                        <form action="{{ route('estudiante.inscribir') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
                            <button type="submit" class="btn-enroll" {{ $estudiante->deuda ? 'disabled' : '' }}>
                                <i class="fa-solid fa-circle-plus"></i>
                                Matricular
                            </button>
                        </form>

                    @endif

                </div>

            </div>

        @empty

            <div class="subject-empty">
                @if($matriculaAbierta)
                    No tienes materias pendientes para este cuatrimestre del plan.
                @else
                    La matrícula para este período está cerrada.
                @endif
            </div>

        @endforelse

    </section>

    <!-- Diálogo de confirmación para desmatricular -->
    <dialog class="confirm-dialog" id="confirm-desinscribir">
        <div class="confirm-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3>Retirar asignatura</h3>
        <p class="confirm-msg">¿Deseas retirar esta asignatura de tu inscripción?</p>
        <span class="confirm-curso" id="confirm-curso"></span>
        <div class="confirm-actions">
            <button type="button" class="btn-exit-list" data-close-confirm>Cancelar</button>
            <button type="button" class="btn-remove" id="confirm-submit">Sí, retirar</button>
        </div>
    </dialog>

</div>

<script>
    // Cerrar los avisos al pulsar la X
    document.querySelectorAll('.alert-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var banner = btn.closest('.alert-banner');
            if (banner) banner.remove();
        });
    });

    // Confirmación con estilo antes de desmatricular
    var confirmDialog = document.getElementById('confirm-desinscribir');
    var pendingForm = null;

    document.querySelectorAll('[data-submit-form]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            pendingForm = document.getElementById(btn.getAttribute('data-submit-form'));
            document.getElementById('confirm-curso').textContent = btn.getAttribute('data-curso');
            confirmDialog.showModal();
        });
    });

    document.querySelectorAll('[data-close-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            confirmDialog.close();
        });
    });

    document.getElementById('confirm-submit').addEventListener('click', function () {
        if (pendingForm) pendingForm.submit();
    });

    // Cerrar si se hace clic fuera del diálogo
    confirmDialog.addEventListener('click', function (e) {
        if (e.target === confirmDialog) confirmDialog.close();
    });
</script>

@endsection