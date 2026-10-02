<x-mail::message>
# Hola, {{ $estudiante }}.

Tu profesor registró una evaluación nueva en el sistema.

**Materia:** {{ $curso }}

@if (filled($parciales))
**Evaluación:** {{ implode(', ', $parciales) }}
@endif

@if ($calificacion !== null)
**Promedio actual (4 parciales de 25 %):** {{ number_format($calificacion, 2) }} / 10
@endif

<x-mail::button :url="route('estudiante.notas')">
    Ver mis notas
</x-mail::button>

El promedio se calcula solo a partir de tus cuatro parciales: cada una pesa el
25 %. Si tienes dudas, contacta a tu docente.

Saludos cordiales,<br>
{{ config('app.name') }}
</x-mail::message>