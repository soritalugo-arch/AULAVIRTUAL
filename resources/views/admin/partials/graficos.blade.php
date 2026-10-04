{{--
    Gráficos del panel: un solo bloque de JS compartido por las secciones.
    Recibe $graficos con las series que necesita la página actual:

        'inscripcion' => inscripcionPorCurso   (ocupado vs cupo libre)
        'carrera'     => inscritosPorCarrera   (torta por carrera)
        'rendimiento' => rendimientoPorCurso   (aprobados / reprobados / en curso)
        'asistencia'  => asistenciaPorCurso    (% de inasistencia con semáforo)

    Cada bloque se dibuja solo si su dato llegó y su <canvas> existe, así una
    página con un solo gráfico no se toca con el resto.
--}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        var G = @json($graficos ?? []);

        var azul = '#4c6fe0', verde = '#0a9560', rojo = '#ec3e67', naranja = '#c2560a';
        var gris = '#b9c4d8';
        var rejilla = 'rgba(224, 232, 245, 0.9)';
        var texto = '#64789f';

        Chart.defaults.font.family = '"DM Sans", sans-serif';
        Chart.defaults.color = texto;

        var opcionesBase = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { boxWidth: 12, boxHeight: 12, font: { size: 12, weight: '600' } } },
                tooltip: { padding: 10, cornerRadius: 10, titleFont: { size: 13 }, bodyFont: { size: 13 } }
            },
            scales: {
                x: { grid: { color: rejilla, drawBorder: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: rejilla, drawBorder: false }, ticks: { font: { size: 11 } } }
            }
        };

        function clonar(objeto) {
            if (Array.isArray(objeto)) return objeto.map(clonar);
            if (objeto === null || typeof objeto !== 'object') return objeto;
            var copia = {};
            Object.keys(objeto).forEach(function (clave) { copia[clave] = clonar(objeto[clave]); });
            return copia;
        }

        var paletaCarrera = ['#4c6fe0', '#6b4fd8', '#0a9560', '#c2560a', '#ec3e67', '#2f9bc4', '#8a7a3f', '#5a6f9c'];

        function seguro(nombre, construir) {
            try { construir(); } catch (e) {
                if (window.console) console.error('No se pudo dibujar el grafico ' + nombre + ':', e);
            }
        }

        var ALTO_BARRA = 30;
        var ALTO_MINIMO = 200;
        var BLOQUE = {{ \App\Services\ReporteService::BLOQUE_CURSOS }};

        function barrasProgressivas(cfg) {
            var canvas = document.getElementById(cfg.canvas);
            if (!canvas) return;

            var total = cfg.datos.length;
            var mostrados = Math.min(BLOQUE, total);
            var serie = cfg.series;
            var vista = cfg.datos.slice(0, mostrados);
            var op = clonar(cfg.opciones);
            op.indexAxis = 'y';

            var chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: serie.map(function (s) {
                        return {
                            label: s.label,
                            data: [],
                            backgroundColor: cfg.colorPorFila ? [] : s.color,
                            borderRadius: 5
                        };
                    })
                },
                options: op
            });

            function pintar() {
                vista = cfg.datos.slice(0, mostrados);
                chart.data.labels = vista.map(function (f) { return f.curso; });
                serie.forEach(function (s, i) {
                    var dataset = chart.data.datasets[i];
                    dataset.data = vista.map(function (f) { return f[s.campo]; });
                    if (cfg.colorPorFila) dataset.backgroundColor = vista.map(cfg.colorPorFila);
                });

                if (cfg.etiqueta) {
                    op.plugins.tooltip.callbacks = {
                        label: function (contexto) { return cfg.etiqueta(vista[contexto.dataIndex]); }
                    };
                }

                document.getElementById(cfg.lienzo).style.height = Math.max(mostrados * ALTO_BARRA + 90, ALTO_MINIMO) + 'px';
                chart.resize();
                chart.update();

                var restantes = total - mostrados;
                document.getElementById(cfg.contador).textContent = 'Mostrando ' + mostrados + ' de ' + total + ' ' + (cfg.unidad || 'cursos');

                var mas = document.getElementById(cfg.mas);
                var menos = document.getElementById(cfg.menos);
                mas.hidden = restantes === 0;
                mas.textContent = restantes <= BLOQUE ? 'Ver las ' + restantes + ' restantes' : 'Ver ' + BLOQUE + ' más';
                menos.hidden = mostrados <= BLOQUE;
            }

            document.getElementById(cfg.mas).addEventListener('click', function () {
                mostrados = Math.min(mostrados + BLOQUE, total);
                pintar();
            });

            document.getElementById(cfg.menos).addEventListener('click', function () {
                mostrados = Math.min(BLOQUE, total);
                pintar();
            });

            pintar();
        }

        // ---------- Inscripción por curso ----------
        if (G.inscripcion && G.inscripcion.length) {
            seguro('inscripcion por curso', function () {
                var opCupos = clonar(opcionesBase);
                opCupos.scales.x.stacked = true;
                opCupos.scales.y.stacked = true;
                opCupos.scales.y.ticks.font = { size: 11 };
                opCupos.plugins.legend.display = false;
                opCupos.scales.x.beginAtZero = true;
                opCupos.scales.x.title = { display: true, text: 'Estudiantes' };
                opCupos.scales.x.ticks = { font: { size: 11 }, precision: 0 };

                barrasProgressivas({
                    canvas: 'grafCupos', lienzo: 'lienzoCupos', contador: 'contadorCupos',
                    mas: 'masCupos', menos: 'menosCupos', datos: G.inscripcion,
                    series: [
                        { campo: 'inscritos', label: 'Ocupado', color: azul },
                        { campo: 'libres', label: 'Cupo libre', color: '#d6e0f4' }
                    ],
                    etiqueta: function (fila) {
                        return [ fila.inscritos + ' de ' + fila.cupo + ' lugares', 'Ocupacion: ' + fila.ocupacion + '%' ];
                    },
                    opciones: opCupos
                });
            });
        }

        // ---------- Estudiantes por carrera ----------
        if (G.carrera && G.carrera.length) {
            var elCarrera = document.getElementById('grafCarrera');
            if (elCarrera) {
                var datosCarrera = {};
                G.carrera.forEach(function (f) {
                    if (f.total > 0) datosCarrera[f.nombre] = f.total;
                });
                seguro('estudiantes por carrera', function () {
                    new Chart(elCarrera, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(datosCarrera),
                            datasets: [{ data: Object.values(datosCarrera), backgroundColor: paletaCarrera, borderColor: '#fff', borderWidth: 2 }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '58%',
                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 11, boxHeight: 11, padding: 12, font: { size: 11, weight: '600' } } } }
                        }
                    });
                });
            }
        }

        // ---------- Rendimiento por curso ----------
        if (G.rendimiento && G.rendimiento.length) {
            seguro('rendimiento por curso', function () {
                var opRend = clonar(opcionesBase);
                opRend.scales.x.stacked = true; opRend.scales.y.stacked = true;
                opRend.scales.y.ticks.font = { size: 11 }; opRend.plugins.legend.position = 'top';

                barrasProgressivas({
                    canvas: 'grafRendimiento', lienzo: 'lienzoRendimiento', contador: 'contadorRendimiento',
                    mas: 'masRendimiento', menos: 'menosRendimiento', datos: G.rendimiento,
                    series: [
                        { campo: 'aprobados', label: 'Aprobados', color: verde },
                        { campo: 'reprobados', label: 'Reprobados', color: rojo },
                        { campo: 'enCurso', label: 'En curso', color: azul }
                    ],
                    opciones: opRend
                });
            });
        }

        // ---------- Asistencia por curso ----------
        if (G.asistencia && G.asistencia.length) {
            seguro('asistencia por curso', function () {
                var opAsis = clonar(opcionesBase);
                opAsis.plugins.legend.display = false;
                opAsis.scales.x.title = { display: true, text: '% de inasistencia' };
                opAsis.scales.x.beginAtZero = true; opAsis.scales.x.suggestedMax = 35;
                opAsis.scales.x.ticks = { font: { size: 11 }, callback: function (v) { return v + '%'; } };

                var sinDatos = @json(\App\Services\ReporteService::SIN_DATOS);

                function colorAlerta(nivel) {
                    if (nivel === 'peligro') return rojo;
                    if (nivel === 'advertencia') return naranja;
                    if (nivel === sinDatos) return gris;
                    return verde;
                }

                barrasProgressivas({
                    canvas: 'grafAsistencia', lienzo: 'lienzoAsistencia', contador: 'contadorAsistencia',
                    mas: 'masAsistencia', menos: 'menosAsistencia', datos: G.asistencia,
                    series: [{ campo: 'porcentaje', label: '% inasistencia', color: verde }],
                    colorPorFila: function (f) { return colorAlerta(f.alerta); },
                    etiqueta: function (fila) {
                        if (!fila || fila.porcentaje === null) return 'Sin asistencias registradas';
                        return fila.porcentaje + '% de inasistencia';
                    },
                    opciones: opAsis
                });
            });
        }
    })();
</script>