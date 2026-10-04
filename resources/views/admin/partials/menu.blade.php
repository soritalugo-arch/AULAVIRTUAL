{{--
    Menú lateral del panel de la Rectora. En pantallas chicas se vuelve una
    fila horizontal deslizable; en pantallas medianas en adelante, una columna.
--}}
<nav class="w-full md:w-64 shrink-0" aria-label="Secciones del panel">
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-3 sm:p-4 flex md:block gap-2 overflow-x-auto">
        <span class="hidden md:block px-3 pt-2 pb-3 text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Secciones</span>

        @php
            $secciones = [
                'admin.dashboard'     => ['Inicio / Resumen', 'fa-house'],
                'admin.inscripciones' => ['Inscripciones', 'fa-chair'],
                'admin.rendimiento'   => ['Rendimiento', 'fa-chart-column'],
                'admin.asistencia'    => ['Asistencia', 'fa-calendar-check'],
                'admin.deudas'        => ['Deudas', 'fa-money-bill-transfer'],
                'admin.asignaciones.index'  => ['Asignaciones', 'fa-chalkboard-user'],
                'admin.plan'          => ['Plan de Estudios', 'fa-book'],
                'admin.periodo'       => ['Periodo Académico', 'fa-calendar-days'],
            ];
        @endphp

        @foreach ($secciones as $ruta => [$etiqueta, $icono])
            <a href="{{ route($ruta) }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-2xl text-sm font-semibold whitespace-nowrap transition-colors
                      {{ request()->routeIs($ruta)
                          ? 'bg-[#eef3ff] text-[#2f55c4]'
                          : 'text-[#5a6f9c] hover:bg-[#f2f6fd]' }}">
                <i class="fa-solid {{ $icono }} w-5 text-center shrink-0"></i>
                {{ $etiqueta }}
            </a>
        @endforeach
    </div>
</nav>