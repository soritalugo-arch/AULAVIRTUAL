@extends('layouts.admin')

@section('titulo', 'Rendimiento · Panel de la Rectora')
@section('tituloPantalla', 'Rendimiento')

@section('panel')

    @php
        $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
        $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
        $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
        $rendimientoConDatos = array_filter($rendimientoPorCurso, fn ($f) => $f['aprobados'] + $f['reprobados'] + $f['enCurso'] > 0);
        $rendEstConDatos = array_filter(
            $rendimientoPorEstudiante,
            fn ($f) => $f['promedio'] !== null || ($f['aprobadas'] + $f['reprobadas']) > 0
        );
    @endphp

    {{-- Rendimiento por curso --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chart-column text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Rendimiento por curso</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap w-fit">{{ $periodoChip }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-6">Aprobados, reprobados y en curso, ordenados por movimiento</p>

        @if ($rendimientoConDatos)
            <div class="relative h-[350px] sm:h-[420px]" id="lienzoRendimiento">
                <canvas id="grafRendimiento"></canvas>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-5 pt-5 border-t border-[#eef3fb]">
                <span class="text-[13px] text-[#7a8db5] mr-auto" id="contadorRendimiento"></span>
                <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl text-[#7a8db5] hover:bg-[#f2f6fd] transition-colors" id="menosRendimiento" hidden>Ver menos</button>
                <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors" id="masRendimiento"></button>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[180px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                <p class="text-sm max-w-[250px]">En {{ $periodo }} todavía no hay notas ni inscripciones registradas.</p>
            </div>
        @endif
    </div>

    {{-- Rendimiento por estudiante --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-ranking-star text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Rendimiento por estudiante</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap">{{ $periodoChip }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Promedio, aprobadas y reprobadas de cada estudiante con matrícula o notas en el periodo.</p>

        @if ($rendEstConDatos)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-[#9aabd0] text-sm pointer-events-none"></i>
                    <input type="search" id="buscarEstudiante" placeholder="Buscar por nombre o cédula…"
                           class="w-full border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold pl-11 pr-4 py-2.5 outline-none focus:border-[#4c5bc3] placeholder:text-[#9aabd0]">
                </div>
                <span class="text-[13px] text-[#7a8db5] sm:ml-auto" id="contadorEstudiantes"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="nombre">Estudiante<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="carrera">Carrera<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="cedula">Cédula<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 pr-4 font-semibold text-right cursor-pointer select-none" data-col="promedio">Promedio<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 pr-4 font-semibold text-right cursor-pointer select-none" data-col="aprobadas">Aprobadas<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 font-semibold text-right cursor-pointer select-none" data-col="reprobadas">Reprobadas<span class="flecha-orden ml-1"></span></th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoEstudiantes" class="text-[#36487a]">
                        @foreach ($rendimientoPorEstudiante as $fila)
                            @if ($fila['promedio'] === null && ($fila['aprobadas'] + $fila['reprobadas']) === 0)
                                @continue
                            @endif
                            <tr data-id="{{ $fila['id'] }}"
                                data-nombre="{{ $fila['estudiante'] }}"
                                data-cedula="{{ $fila['cedula'] }}"
                                data-carrera="{{ $fila['carrera'] }}"
                                data-promedio="{{ $fila['promedio'] !== null ? $fila['promedio'] : '' }}"
                                data-aprobadas="{{ $fila['aprobadas'] }}"
                                data-reprobadas="{{ $fila['reprobadas'] }}"
                                data-href="{{ route('admin.rendimiento.estudiante', ['estudiante' => $fila['id'], 'cuatrimestre' => $idCuatrimestre]) }}"
                                class="border-b border-[#f2f6fd] last:border-0 hover:bg-[#f7f9ff] transition-colors cursor-pointer">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('admin.rendimiento.estudiante', ['estudiante' => $fila['id'], 'cuatrimestre' => $idCuatrimestre]) }}"
                                       class="font-semibold text-[#171c7c] hover:text-[#2f55c4] underline decoration-transparent hover:decoration-[#2f55c4]/40 underline-offset-2">
                                        {{ $fila['estudiante'] }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4">{{ $fila['carrera'] }}</td>
                                <td class="py-3 pr-4">{{ $fila['cedula'] }}</td>
                                <td class="py-3 pr-4 text-right font-bold text-[#2f55c4]">{{ $fila['promedio'] !== null ? number_format($fila['promedio'], 2) : '—' }}</td>
                                <td class="py-3 pr-4 text-right text-[#0a9560]">{{ $fila['aprobadas'] }}</td>
                                <td class="py-3 text-right text-[#ec3e67]">{{ $fila['reprobadas'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-[#eef3fb]">
                <button type="button" id="anteriorEstudiantes" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">← Anterior</button>
                <span class="text-[13px] text-[#7a8db5]" id="paginacionEstudiantes"></span>
                <button type="button" id="siguienteEstudiantes" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Siguiente →</button>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                <p class="text-sm max-w-[300px]">En {{ $periodo }} todavía no hay estudiantes con matrícula ni notas registradas.</p>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    @include('admin.partials.graficos', ['graficos' => ['rendimiento' => $rendimientoPorCurso]])

    {{-- Búsqueda, orden y paginación de la tabla de estudiantes (todo en el cliente) --}}
    <script>
        (function () {
            var cuerpo = document.getElementById('cuerpoEstudiantes');
            if (!cuerpo) return;

            var filasOriginales = Array.prototype.slice.call(cuerpo.querySelectorAll('tr[data-id]'));
            if (!filasOriginales.length) return;

            var POR_PAGINA = 25;
            var estado = { q: '', campo: 'promedio', dir: 1, pagina: 1 };

            var input = document.getElementById('buscarEstudiante');
            var anterior = document.getElementById('anteriorEstudiantes');
            var siguiente = document.getElementById('siguienteEstudiantes');
            var contador = document.getElementById('contadorEstudiantes');
            var paginacion = document.getElementById('paginacionEstudiantes');
            var cabeceras = Array.prototype.slice.call(document.querySelectorAll('th[data-col]'));

            function valor(fila, campo) {
                var v = fila.getAttribute('data-' + campo) || '';
                if (campo === 'promedio' || campo === 'aprobadas' || campo === 'reprobadas') {
                    return v === '' ? null : parseFloat(v);
                }
                return v.toLowerCase();
            }

            function visibles() {
                var q = estado.q.toLowerCase();

                var filtradas = filasOriginales.filter(function (fila) {
                    if (!q) return true;
                    return (valor(fila, 'nombre') + ' ' + valor(fila, 'cedula')).indexOf(q) !== -1;
                });

                if (estado.campo) {
                    var campo = estado.campo;
                    var dir = estado.dir;
                    filtradas.sort(function (a, b) {
                        var va = valor(a, campo);
                        var vb = valor(b, campo);
                        if (va === null && vb === null) return 0;
                        if (va === null) return 1;      // los sin dato, al final
                        if (vb === null) return -1;
                        if (va < vb) return -1 * dir;
                        if (va > vb) return 1 * dir;
                        return 0;
                    });
                }

                return filtradas;
            }

            function pintar() {
                var lista = visibles();
                var total = lista.length;
                var paginas = Math.max(1, Math.ceil(total / POR_PAGINA));
                if (estado.pagina > paginas) estado.pagina = paginas;

                var pagina = lista.slice((estado.pagina - 1) * POR_PAGINA, estado.pagina * POR_PAGINA);

                // Quitar lo que haya (filas de la pagina anterior) y borrar el aviso de vacio.
                cuerpo.querySelectorAll('tr[data-id], tr[data-vacio]').forEach(function (tr) { tr.remove(); });

                if (!pagina.length) {
                    var tr = document.createElement('tr');
                    tr.setAttribute('data-vacio', '1');
                    tr.innerHTML = '<td colspan="6" class="py-8 text-center text-[#8a9cc0] text-sm">Ningún estudiante coincide con esa búsqueda.</td>';
                    cuerpo.appendChild(tr);
                } else {
                    var fragmento = document.createDocumentFragment();
                    pagina.forEach(function (tr) { fragmento.appendChild(tr); });
                    cuerpo.appendChild(fragmento);
                }

                var desde = total === 0 ? 0 : (estado.pagina - 1) * POR_PAGINA + 1;
                var hasta = Math.min(estado.pagina * POR_PAGINA, total);
                contador.textContent = 'Mostrando ' + (total === 0 ? '0' : desde + '–' + hasta) + ' de ' + total + ' estudiantes';
                paginacion.textContent = 'Página ' + estado.pagina + ' de ' + paginas;
                anterior.disabled = estado.pagina <= 1;
                siguiente.disabled = estado.pagina >= paginas;
            }

            function marcarOrden() {
                cabeceras.forEach(function (th) {
                    var flecha = th.querySelector('.flecha-orden');
                    if (!flecha) return;
                    if (th.getAttribute('data-col') === estado.campo) {
                        flecha.textContent = estado.dir === 1 ? '▲' : '▼';
                        th.classList.add('text-[#2f55c4]');
                        th.classList.remove('text-[#7a8db5]');
                    } else {
                        flecha.textContent = '';
                        th.classList.remove('text-[#2f55c4]');
                        th.classList.add('text-[#7a8db5]');
                    }
                });
            }

            filasOriginales.forEach(function (tr) {
                tr.addEventListener('click', function (e) {
                    if (e.target.closest('a')) return; // el enlace del nombre ya navega
                    location.href = tr.getAttribute('data-href');
                });
            });

            cabeceras.forEach(function (th) {
                th.addEventListener('click', function () {
                    var campo = th.getAttribute('data-col');
                    if (estado.campo === campo) {
                        estado.dir = estado.dir === 1 ? -1 : 1;
                    } else {
                        estado.campo = campo;
                        estado.dir = 1;
                    }
                    estado.pagina = 1;
                    marcarOrden();
                    pintar();
                });
            });

            input.addEventListener('input', function () {
                estado.q = input.value;
                estado.pagina = 1;
                pintar();
            });

            anterior.addEventListener('click', function () {
                if (estado.pagina > 1) { estado.pagina--; pintar(); }
            });

            siguiente.addEventListener('click', function () {
                if (!siguiente.disabled) { estado.pagina++; pintar(); }
            });

            marcarOrden();
            pintar();
        })();
    </script>
@endpush