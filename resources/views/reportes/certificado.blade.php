{{-- Certificado de notas del estudiante, plantilla de dompdf.

     Restricciones de dompdf que guia el marcado: nada de CSS grid ni flexbox,
     nada de assets externos y sin iconos de Font Awesome (se verian como
     cuadritos vacios). Solo tablas, anchos fijos y estilos embebidos. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado de notas</title>
    <style>
        @page {
            margin: 34px 30px 40px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5px;
            color: #19325f;
            margin: 0;
        }

        /* ── Encabezado ────────────────────────────────────────────── */

        .cabecera { border-bottom: 3px solid #4c5bc3; padding-bottom: 12px; }

        .institucion { font-size: 15px; font-weight: bold; color: #171d7d; }

        .institucion span { color: #5a73d2; }

        .sub { font-size: 9px; color: #7087ba; margin-top: 2px; }

        .titulo-doc {
            margin-top: 10px;
            font-size: 19px;
            font-weight: bold;
            color: #171d7d;
            letter-spacing: 1.4px;
        }

        .sello {
            float: right;
            text-align: right;
            font-size: 9px;
            color: #7087ba;
            line-height: 1.5;
        }

        .sello b { display: block; font-size: 10px; color: #19325f; }

        /* ── Datos del estudiante ─────────────────────────────────── */

        .datos {
            width: 100%;
            margin-top: 16px;
            border: 1px solid #d9e6fb;
            background-color: #f4f8ff;
        }

        .datos td {
            padding: 8px 12px;
            font-size: 10.5px;
            border-bottom: 1px solid #e6eefb;
        }

        .datos tr:last-child td { border-bottom: 0; }

        .datos .etiqueta {
            width: 22%;
            color: #7087ba;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .datos .valor { width: 28%; color: #171d7d; font-weight: bold; }

        /* ── Resumen ──────────────────────────────────────────────── */

        .resumen { width: 100%; margin-top: 14px; }

        .resumen td {
            width: 25%;
            padding: 9px 10px;
            text-align: center;
            border: 1px solid #d9e6fb;
            background-color: #ffffff;
        }

        .resumen .rotulo {
            display: block;
            font-size: 7.5px;
            color: #7087ba;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .resumen .cifra {
            display: block;
            margin-top: 3px;
            font-size: 15px;
            font-weight: bold;
            color: #171d7d;
        }

        /* ── Tablas por cuatrimestre ──────────────────────────────── */

        .periodo { margin-top: 18px; page-break-inside: avoid; }

        .periodo-cabecera {
            padding: 7px 10px;
            background-color: #eaf1fb;
            border: 1px solid #d9e6fb;
        }

        .periodo-cabecera b { color: #171d7d; font-size: 11px; }

        .periodo-cabecera span { color: #6d86b8; font-size: 9px; }

        .tabla { width: 100%; border-collapse: collapse; margin-top: -1px; }

        .tabla th {
            padding: 7px 9px;
            font-size: 7.5px;
            color: #7087ba;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: left;
            background-color: #f4f8ff;
            border: 1px solid #d9e6fb;
        }

        .tabla td {
            padding: 7px 9px;
            font-size: 9.5px;
            border: 1px solid #d9e6fb;
        }

        .tabla .c-curso { width: 46%; }
        .tabla .c-nota { width: 10%; text-align: center; }
        .tabla .c-inasistencia { width: 16%; text-align: center; }
        .tabla .c-estado { width: 28%; }

        .punto { color: #a7b7d3; }

        .nota { font-weight: bold; color: #6680b5; }
        .nota.aprobado { color: #079263; }
        .nota.reprobado { color: #d23a5f; }

        .pill {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 12px;
            font-size: 8.5px;
            font-weight: bold;
        }

        .pill.aprobado { background-color: #d9f9e8; color: #079263; }
        .pill.reprobado { background-color: #ffe0e8; color: #ec3e67; }
        .pill.en-curso { background-color: #eaf1fb; color: #4f72b4; }
        .pill.presunto { background-color: #fff4d4; color: #c78d18; }

        /* ── Sin notas ────────────────────────────────────────────── */

        .sin-notas {
            margin-top: 18px;
            padding: 24px;
            text-align: center;
            border: 1px dashed #b9ccef;
            background-color: #f7faff;
        }

        .sin-notas b { display: block; margin-bottom: 5px; font-size: 12px; color: #19325f; }

        .sin-notas span { font-size: 9.5px; color: #7087ba; }

        .nota-pie { margin-top: 7px; font-size: 8.5px; color: #7087ba; }

        /* ── Firma ────────────────────────────────────────────────── */

        .firma { width: 100%; margin-top: 34px; page-break-inside: avoid; }

        .firma td { width: 33%; vertical-align: bottom; }

        .firma .linea {
            border-top: 1px solid #8fa4cf;
            margin: 0 8px 5px;
            height: 34px;
        }

        .firma .cargo { text-align: center; font-size: 8.5px; color: #7087ba; }

        .firma .cargo b { display: block; font-size: 9.5px; color: #19325f; }

        .pie {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #d9e6fb;
            font-size: 7.5px;
            color: #8ba0c8;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Encabezado de la institución --}}
    <div class="cabecera">
        <div class="sello">
            <b>{{ config('app.name') }}</b>
            Secretaría Académica<br>
            Certificado generado el {{ $emitido->format('d/m/Y') }}<br>
            N.º {{ str_pad((string) $estudiante->id_usuario, 6, '0', STR_PAD_LEFT) }}
        </div>

        <div class="institucion"><span>Aula</span>Virtual</div>
        <div class="sub">Plataforma académica de gestión de cursos por cuatrimestre</div>

        <div class="titulo-doc">CERTIFICADO DE NOTAS</div>
    </div>

    {{-- Datos del estudiante --}}
    <table class="datos">
        <tr>
            <td class="etiqueta">Estudiante</td>
            <td class="valor" colspan="3">
                {{ $estudiante->usuario->nombres }} {{ $estudiante->usuario->apellidos }}
            </td>
        </tr>
        <tr>
            <td class="etiqueta">Cédula</td>
            <td class="valor">{{ $estudiante->cedula ?: 'No registrada' }}</td>
            <td class="etiqueta">Correo</td>
            <td class="valor">{{ $estudiante->usuario->email }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Carrera</td>
            <td class="valor" colspan="3">
                {{ $estudiante->carrera?->nombre ?? 'No registrada' }}
            </td>
        </tr>
    </table>

    @php $resumen = $datos['resumen']; @endphp

    {{-- Cifras de toda la carrera --}}
    <table class="resumen">
        <tr>
            <td>
                <span class="rotulo">Promedio general</span>
                <span class="cifra">{{ $resumen['promedio'] !== null ? number_format($resumen['promedio'], 2) : '—' }}</span>
            </td>
            <td>
                <span class="rotulo">Cursos aprobados</span>
                <span class="cifra">{{ number_format($resumen['aprobados']) }}</span>
            </td>
            <td>
                <span class="rotulo">Cursos reprobados</span>
                <span class="cifra">{{ number_format($resumen['reprobados']) }}</span>
            </td>
            <td>
                <span class="rotulo">Cuatrimestres cursados</span>
                <span class="cifra">{{ number_format($resumen['cuatrimestres']) }}</span>
            </td>
        </tr>
    </table>

    @if ($datos['periodos']->isEmpty())
        {{-- Sin notas cargadas el certificado sale igual, pero lo dice: un
             certificado en blanco no puede leerse como "no reprobaste nada". --}}
        <div class="sin-notas">
            <b>No hay notas registradas a la fecha de emisión</b>
            <span>
                Este certificado se emite sin resultados porque aún no se ha
                cargado ninguna calificación para este estudiante.
            </span>
        </div>
    @else
        {{-- Una tabla por cuatrimestre, con su promedio --}}
        @foreach ($datos['periodos'] as $periodo)
            <div class="periodo">
                <div class="periodo-cabecera">
                    <b>{{ $periodo['codigo'] }}</b>
                    <span>
                        · {{ $periodo['cuatrimestre']->fecha_inicio->format('d/m/Y') }} al
                        {{ $periodo['cuatrimestre']->fecha_fin->format('d/m/Y') }}
                        · Promedio {{ $periodo['promedio'] !== null ? number_format($periodo['promedio'], 2) : '—' }}
                        · {{ $periodo['aprobados'] }} aprobado{{ $periodo['aprobados'] === 1 ? '' : 's' }}
                        @if ($periodo['reprobados'] > 0)
                            · {{ $periodo['reprobados'] }} reprobado{{ $periodo['reprobados'] === 1 ? '' : 's' }}
                        @endif
                    </span>
                </div>

                <table class="tabla">
                    <thead>
                        <tr>
                            <th class="c-curso">Curso</th>
                            <th class="c-nota">Nota</th>
                            <th class="c-inasistencia">Inasistencia</th>
                            <th class="c-estado">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($periodo['cursos'] as $curso)
                            @php
                                // La pildora sale del veredicto del servicio de
                                // reglas, no de comparar la nota con el 6 aca: un 9
                                // con 40 % de faltas es reprobado y el certificado
                                // tiene que decirlo igual que el modulo del alumno.
                                $pildora = match ($curso['estado']) {
                                    'Reprobado (presunto)' => ['presunto', 'Reprobado (presunto)'],
                                    'En curso' => ['en-curso', 'En curso'],
                                    'Aprobado' => ['aprobado', 'Aprobado'],
                                    default => ['reprobado', 'Reprobado'],
                                };
                            @endphp
                            <tr>
                                <td>{{ $curso['curso'] }}</td>
                                <td class="c-nota">
                                    @if ($curso['nota'] === null)
                                        <span class="punto">—</span>
                                    @else
                                        <span class="nota {{ $pildora[0] === 'aprobado' ? 'aprobado' : 'reprobado' }}">{{ $curso['nota'] }}</span>
                                    @endif
                                </td>
                                <td class="c-inasistencia">{{ number_format($curso['inasistencia'], 1) }} %</td>
                                <td class="c-estado">
                                    <span class="pill {{ $pildora[0] }}">{{ $pildora[1] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if ($datos['enCurso']->isNotEmpty())
            {{-- Los cursos en curso no entran en el certificado: no tienen nota
                 que certificar. Se mencionan aparte para que el documento no
                 parezca completo cuando no lo esta. --}}
            <p class="nota-pie">
                Quedan {{ $datos['enCurso']->count() }}
                {{ $datos['enCurso']->count() === 1 ? 'curso matriculado' : 'cursos matriculados' }}
                en el cuatrimestre
                {{ 'Q'.str_pad((int) $datos['enCursoCuatrimestre']->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                sin nota registrada; no forman parte de este certificado.
            </p>
        @endif
    @endif

    {{-- Firma y fecha --}}
    <table class="firma">
        <tr>
            <td><div class="linea"></div><div class="cargo"><b>Secretaría Académica</b>Firma</div></td>
            <td><div class="linea"></div><div class="cargo"><b>Dirección Académica</b>Firma</div></td>
            <td><div class="linea"></div><div class="cargo"><b>Fecha de emisión</b>{{ $emitido->format('d/m/Y') }}</div></td>
        </tr>
    </table>

    <p class="pie">
        Documento generado automáticamente por {{ config('app.name') }} a partir de las
        calificaciones registradas en la plataforma. No requiere firma digital.
    </p>

</body>
</html>
