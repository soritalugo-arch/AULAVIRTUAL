<x-mail::message>
# Hola, {{ $estudiante }}.

Tu profesor ha publicado una nueva calificación en el sistema.

**Materia:** {{ $curso }}  
**Calificación Final:** {{ $calificacion }}

<x-mail::button :url="url('/estudiante/historial-academico')">
Ver Historial Académico
</x-mail::button>

Si tienes dudas, contacta a tu docente.

Saludos cordiales,<br>
{{ config('app.name') }}
</x-mail::message>