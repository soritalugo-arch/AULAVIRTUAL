@extends('layouts.admin')

@section('titulo', 'Deudas · Panel de la Rectora')
@section('tituloPantalla', 'Deudas')

@section('panel')

    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-[22px] text-[#ec3e67]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Estudiantes con deuda</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#ffe7ec] text-[#ec3e67] text-xs font-bold whitespace-nowrap w-fit">{{ $deudores->count() }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Tienen bloqueada la matrícula mientras no regularicen su estado de cuenta. Haz clic en un estudiante para ver su ficha.</p>

        @if ($deudores->isNotEmpty())
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-[#9aabd0] text-sm pointer-events-none"></i>
                    <input type="search" id="buscarDeudor" placeholder="Buscar por nombre o cédula…"
                           class="w-full border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold pl-11 pr-4 py-2.5 outline-none focus:border-[#4c5bc3] placeholder:text-[#9aabd0]">
                </div>
                <span class="text-[13px] text-[#7a8db5] sm:ml-auto" id="contadorDeudores"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="nombre">Estudiante<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="cedula">Cédula<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 font-semibold cursor-pointer select-none" data-col="carrera">Carrera<span class="flecha-orden ml-1"></span></th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoDeudores" class="text-[#36487a]">
                        @foreach ($deudores as $d)
                            @php
                                $ficha = route('admin.rendimiento.estudiante', [
                                    'estudiante' => $d->id_usuario,
                                    'cuatrimestre' => $idCuatrimestre,
                                ]);
                            @endphp
                            <tr data-id="{{ $d->id_usuario }}"
                                data-nombre="{{ $d->usuario?->nombres }} {{ $d->usuario?->apellidos }}"
                                data-cedula="{{ $d->cedula }}"
                                data-carrera="{{ $d->carrera?->nombre }}"
                                data-href="{{ $ficha }}"
                                class="border-b border-[#f2f6fd] last:border-0 hover:bg-[#f7f9ff] transition-colors cursor-pointer">
                                <td class="py-3 pr-4">
                                    <a href="{{ $ficha }}"
                                       class="font-semibold text-[#171c7c] hover:text-[#2f55c4] underline decoration-transparent hover:decoration-[#2f55c4]/40 underline-offset-2">
                                        {{ $d->usuario?->nombres }} {{ $d->usuario?->apellidos }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4">{{ $d->cedula }}</td>
                                <td class="py-3">{{ $d->carrera?->nombre }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-[#eef3fb]">
                <button type="button" id="anteriorDeudores" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">← Anterior</button>
                <span class="text-[13px] text-[#7a8db5]" id="paginacionDeudores"></span>
                <button type="button" id="siguienteDeudores" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Siguiente →</button>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-circle-check text-3xl text-[#0a9560]"></i>
                <p class="text-sm">No hay estudiantes con deuda registrada.</p>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    {{-- Búsqueda, orden y paginación de la tabla de deudores (todo en el cliente) --}}
    <script>
        (function () {
            var cuerpo = document.getElementById('cuerpoDeudores');
            if (!cuerpo) return;

            var filasOriginales = Array.prototype.slice.call(cuerpo.querySelectorAll('tr[data-id]'));
            if (!filasOriginales.length) return;

            var POR_PAGINA = 25;
            var estado = { q: '', campo: 'nombre', dir: 1, pagina: 1 };

            var input = document.getElementById('buscarDeudor');
            var anterior = document.getElementById('anteriorDeudores');
            var siguiente = document.getElementById('siguienteDeudores');
            var contador = document.getElementById('contadorDeudores');
            var paginacion = document.getElementById('paginacionDeudores');
            var cabeceras = Array.prototype.slice.call(document.querySelectorAll('th[data-col]'));

            function valor(fila, campo) {
                return (fila.getAttribute('data-' + campo) || '').toLowerCase();
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
                        if (va === vb) return 0;
                        return (va < vb ? -1 : 1) * dir;
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

                // Quitar lo que haya (filas de la página anterior) y borrar el aviso de vacío.
                cuerpo.querySelectorAll('tr[data-id], tr[data-vacio]').forEach(function (tr) { tr.remove(); });

                if (!pagina.length) {
                    var tr = document.createElement('tr');
                    tr.setAttribute('data-vacio', '1');
                    tr.innerHTML = '<td colspan="3" class="py-8 text-center text-[#8a9cc0] text-sm">Ningún estudiante coincide con esa búsqueda.</td>';
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