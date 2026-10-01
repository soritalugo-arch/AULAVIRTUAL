<x-mail::message>
# ¡Prepárate, {{ $estudiante }}!

Te recordamos que tu curso está a punto de iniciar.

**Materia:** {{ $curso }}  
**Fecha de Inicio:** {{ $fechaInicio }}

<x-mail::button :url="url('/estudiante/mi-horario')">
Ver mi Horario y Aulas
</x-mail::button>

¡Mucho éxito en este nuevo ciclo!

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>