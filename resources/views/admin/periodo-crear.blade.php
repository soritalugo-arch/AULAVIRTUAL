@extends('layouts.admin')

@section('titulo', 'Crear Período · Panel de la Rectora')
@section('tituloPantalla', 'Nuevo Período Académico')

@section('panel')
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-11 h-11 rounded-2xl bg-[#e8f7ee] flex items-center justify-center shrink-0">
                <i class="fa-solid fa-calendar-plus text-[#1d7a46]"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-['Georgia'] font-bold text-[#171c7c]">Apertura de nuevo período</h2>
                <p class="text-[13px] text-[#7a8db5] mt-1">Define las fechas de inicio y fin del nuevo cuatrimestre.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.periodo.guardar') }}">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-bold text-[#24356e] mb-2">Fecha de inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required
                           class="w-full border border-[#dce7fa] rounded-xl px-4 py-2.5 text-sm text-[#24356e] focus:ring-2 focus:ring-[#2f55c4] focus:border-[#2f55c4] transition-all bg-[#f7f9ff]">
                    @error('fecha_inicio')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_fin" class="block text-sm font-bold text-[#24356e] mb-2">Fecha de fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required
                           class="w-full border border-[#dce7fa] rounded-xl px-4 py-2.5 text-sm text-[#24356e] focus:ring-2 focus:ring-[#2f55c4] focus:border-[#2f55c4] transition-all bg-[#f7f9ff]">
                    @error('fecha_fin')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#0a9560] hover:bg-[#08774d] text-white text-sm font-bold shadow-[0_6px_15px_rgba(10,149,96,0.25)] transition-all">
                    Guardar e iniciar período
                </button>
                <a href="{{ route('admin.periodo') }}"
                   class="px-5 py-2.5 rounded-xl bg-[#eef1f6] hover:bg-[#dce2ec] text-[#66748f] text-sm font-bold transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection