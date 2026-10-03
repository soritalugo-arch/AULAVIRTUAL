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
                    <span>Vas en:</span>
                    <strong>
                        {{ $formato
                            ? $formato . ' de ' . ($totalEtapas ?: $formato)
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

            @if ($ventana && $ventana['desde'] !== $ventana['hasta'])
                <p class="module-description">
                    Repites las materias que te quedaron pendientes y adelantas materias del cuatrimestre siguiente.
                </p>
            @endif

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



@endsection

@push('styles')
    @vite('resources/css/estudiante/matriculacion.css')
@endpush

@push('scripts')
    @vite('resources/js/estudiante/matriculacion.js')
@endpush