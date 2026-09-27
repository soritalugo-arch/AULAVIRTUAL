@extends('layouts.app')

@section('titulo', 'Matriculación de Asignaturas')

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
        color: #202020;

        font-size: 13px;

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
        min-width: 143px;

        height: 36px;

        padding: 0 17px;

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
        margin-right: 7px;
    }


    .btn-remove {
        min-width: 160px;

        height: 36px;

        padding: 0 17px;

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
        margin-right: 7px;
    }


    /* Botón secundario "Salir de lista" */

    .btn-exit-list {
        height: 36px;

        padding: 0 16px;

        border-radius: 20px;

        border: 1px solid #d3dff2;

        background: #ffffff;

        color: #4a618f;

        font-family: inherit;

        font-weight: 600;

        font-size: 12px;

        white-space: nowrap;

        transition: 0.2s ease;
    }

    .btn-exit-list:hover {
        background: #f2f6ff;
    }

    .btn-exit-list i {
        margin-right: 6px;
    }


    .btn-enroll:disabled,
    .btn-remove:disabled {
        opacity: 0.45;

        cursor: not-allowed;

        transform: none;

        box-shadow: none;
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


    /* ================================
       RESPONSIVE
    ================================ */

    @media (max-width: 900px) {

        .module-card {
            padding: 25px;

            align-items: flex-start;
        }

        .module-icon {
            width: 90px;
            height: 90px;

            font-size: 38px;
        }

        .module-info h1 {
            font-size: 28px;
        }

        .table-header,
        .subject-row {
            grid-template-columns:
                38%
                35%
                27%;
        }

    }

    @media (max-width: 700px) {

        .module-card {
            flex-direction: column;
        }

        .academic-card {
            overflow-x: auto;
        }

        .table-header,
        .subject-row {
            min-width: 800px;
        }

        .module-description {
            line-height: 1.5;
        }
    }
</style>

<div class="matriculacion-wrap">

    <!-- Mensajes de estado -->
    @if(session('success'))
    <div class="alert-banner alert-success" role="alert">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('info'))
    <div class="alert-banner alert-info" role="alert">
        <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert-banner alert-error" role="alert">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
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
            <i class="fa-solid fa-graduation-cap"></i>
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


    <!-- OFERTA ACADÉMICA -->
    <section class="academic-card">

        <div class="academic-title">
            <i class="fa-regular fa-book-open"></i>
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

                    @if($enEspera)

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

                        <form action="{{ route('estudiante.desinscribir') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
                            <button type="submit" class="btn-remove" onclick="return confirm('¿Deseas retirar esta asignatura?')">
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
                No hay asignaturas disponibles para tu carrera en este momento.
            </div>

        @endforelse

    </section>

</div>

@endsection