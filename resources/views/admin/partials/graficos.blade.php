{{--
    Gráficos del panel: un solo bloque de JS compartido por las secciones.
    Recibe $graficos con las series que necesita la página actual:

        'inscripcion' => inscripcionPorCurso   (ocupado vs cupo libre)
        'carrera'     => inscritosPorCarrera   (torta por carrera)
        'rendimiento' => rendimientoPorCurso   (aprobados / reprobados / en curso)
        'asistencia'  => asistenciaPorCurso    (% de inasistencia con semáforo)

    Cada bloque se dibuja solo si su dato llegó y su <canvas> existe, así una
    página con un solo gráfico no se toca con el resto.

    El dibujado vive en resources/js/admin/graficos.js. Este partial solo le
    pasa los datos que PHP conoce y carga Chart.js, que sigue viniendo del CDN.
    Se incluye dentro de un @push('scripts'), por eso no vuelve a empujar.
--}}
@php
    $configGraficos = [
        'series' => $graficos ?? [],
        'bloque' => \App\Services\ReporteService::BLOQUE_CURSOS,
        'sinDatos' => \App\Services\ReporteService::SIN_DATOS,
    ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script type="application/json" id="datos-graficos">{!! json_encode($configGraficos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@vite('resources/js/admin/graficos.js')