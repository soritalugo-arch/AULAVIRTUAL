<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;

/**
 * Catalogo completo de materias del instituto.
 *
 * Cada materia dice a que carreras pertenece y en que etapa del plan de esa
 * carrera se dicta (1 = primer cuatrimestre del recorrido, 2 = el segundo...).
 * Las cinco materias base (Matematica, Ingles, Ofimatica, Expresion Oral y
 * Emprendimiento) se comparten entre carreras; el resto son especialidades de
 * cada carrera. Asi cada carrera recorre de 12 a 15 materias en sus 5 o 6
 * cuatrimestres (2 a 3 materias por cuatrimestre).
 */
class CursoSeeder extends Seeder
{
    /** Total de cursos del catalogo; se verifica en run() para no romper los demás seeders. */
    public const TOTAL_CURSOS = 69;

    public const CATALOGO = [
        // ── Materias base (compartidas entre carreras) ────────────────────
        ['nombre' => 'Matemática Básica', 'base' => true, 'carreras' => [
            'Informática' => 1, 'Contaduría' => 1, 'Administración' => 1, 'Electrónica' => 1,
        ]],
        ['nombre' => 'Inglés Técnico', 'base' => true, 'carreras' => [
            'Informática' => 2, 'Contaduría' => 1, 'Administración' => 1, 'Marketing Digital' => 1, 'Turismo' => 1, 'Enfermería' => 2,
        ]],
        ['nombre' => 'Ofimática', 'base' => true, 'carreras' => [
            'Informática' => 1, 'Contaduría' => 1, 'Administración' => 1, 'Marketing Digital' => 1, 'Turismo' => 1, 'Enfermería' => 1,
        ]],
        ['nombre' => 'Expresión Oral y Escrita', 'base' => true, 'carreras' => [
            'Administración' => 2, 'Marketing Digital' => 1, 'Turismo' => 2, 'Diseño Gráfico' => 1,
        ]],
        ['nombre' => 'Emprendimiento', 'base' => true, 'carreras' => [
            'Informática' => 3, 'Administración' => 3, 'Contaduría' => 2, 'Diseño Gráfico' => 2, 'Marketing Digital' => 2, 'Electrónica' => 2,
        ]],

        // ── Informática ─────────────────────────────────────────────────────
        ['nombre' => 'Fundamentos de Programación', 'carreras' => ['Informática' => 2, 'Electrónica' => 3]],
        ['nombre' => 'Base de Datos I', 'carreras' => ['Informática' => 3]],
        ['nombre' => 'Programación II', 'carreras' => ['Informática' => 4]],
        ['nombre' => 'Redes de Computadoras', 'carreras' => ['Informática' => 5, 'Electrónica' => 5]],
        ['nombre' => 'Desarrollo Web', 'carreras' => ['Informática' => 5]],
        ['nombre' => 'Sistemas Operativos', 'carreras' => ['Informática' => 4]],

        // ── Administración ──────────────────────────────────────────────────
        ['nombre' => 'Administración General', 'carreras' => ['Administración' => 2, 'Contaduría' => 3]],
        ['nombre' => 'Gestión de Recursos Humanos', 'carreras' => ['Administración' => 4]],
        ['nombre' => 'Finanzas Empresariales', 'carreras' => ['Administración' => 5]],
        ['nombre' => 'Derecho Laboral', 'carreras' => ['Administración' => 6]],
        ['nombre' => 'Mercadotecnia', 'carreras' => ['Administración' => 4, 'Turismo' => 5]],

        // ── Contaduría ──────────────────────────────────────────────────────
        ['nombre' => 'Contabilidad I', 'carreras' => ['Administración' => 3, 'Contaduría' => 2]],
        ['nombre' => 'Contabilidad II', 'carreras' => ['Contaduría' => 3]],
        ['nombre' => 'Contabilidad de Costos', 'carreras' => ['Contaduría' => 4]],
        ['nombre' => 'Impuestos I', 'carreras' => ['Contaduría' => 5]],
        ['nombre' => 'Auditoría Básica', 'carreras' => ['Contaduría' => 5]],
        ['nombre' => 'Matemática Financiera', 'carreras' => ['Administración' => 6, 'Contaduría' => 4]],

        // ── Diseño Gráfico ──────────────────────────────────────────────────
        ['nombre' => 'Fundamentos del Diseño', 'carreras' => ['Diseño Gráfico' => 1, 'Marketing Digital' => 3]],
        ['nombre' => 'Ilustración Digital', 'carreras' => ['Diseño Gráfico' => 3]],
        ['nombre' => 'Diseño Editorial', 'carreras' => ['Diseño Gráfico' => 3]],
        ['nombre' => 'Diseño Publicitario', 'carreras' => ['Diseño Gráfico' => 4, 'Marketing Digital' => 4]],
        ['nombre' => 'Fundamentos de Color y Composición', 'carreras' => ['Diseño Gráfico' => 2]],

        // ── Marketing Digital ───────────────────────────────────────────────
        ['nombre' => 'Marketing Digital I', 'carreras' => ['Marketing Digital' => 2]],
        ['nombre' => 'Community Management', 'carreras' => ['Marketing Digital' => 3, 'Diseño Gráfico' => 4]],
        ['nombre' => 'SEO y SEM', 'carreras' => ['Marketing Digital' => 4]],
        ['nombre' => 'Analítica Web', 'carreras' => ['Marketing Digital' => 5]],
        ['nombre' => 'Estrategias de Contenido', 'carreras' => ['Marketing Digital' => 5, 'Diseño Gráfico' => 5]],

        // ── Turismo ─────────────────────────────────────────────────────────
        ['nombre' => 'Fundamentos del Turismo', 'carreras' => ['Turismo' => 2]],
        ['nombre' => 'Gestión Hotelera', 'carreras' => ['Turismo' => 3]],
        ['nombre' => 'Ecoturismo', 'carreras' => ['Turismo' => 4]],
        ['nombre' => 'Atención al Cliente y Protocolo', 'carreras' => ['Turismo' => 3, 'Administración' => 5]],
        ['nombre' => 'Turismo Sostenible', 'carreras' => ['Turismo' => 4]],

        // ── Enfermería ──────────────────────────────────────────────────────
        ['nombre' => 'Anatomía y Fisiología', 'carreras' => ['Enfermería' => 1]],
        ['nombre' => 'Enfermería Básica', 'carreras' => ['Enfermería' => 2]],
        ['nombre' => 'Farmacología General', 'carreras' => ['Enfermería' => 3]],
        ['nombre' => 'Primeros Auxilios', 'carreras' => ['Enfermería' => 4]],
        ['nombre' => 'Ética Profesional en Salud', 'carreras' => ['Enfermería' => 3]],

        // ── Electrónica ─────────────────────────────────────────────────────
        ['nombre' => 'Electrónica Básica', 'carreras' => ['Electrónica' => 1]],
        ['nombre' => 'Circuitos Digitales', 'carreras' => ['Electrónica' => 2]],
        ['nombre' => 'Instalaciones Eléctricas', 'carreras' => ['Electrónica' => 4]],

        // ── Especialidades nuevas del plan (Informática) ────────────────────
        ['nombre' => 'Seguridad Informática', 'carreras' => ['Informática' => 6]],
        ['nombre' => 'Programación Avanzada', 'carreras' => ['Informática' => 6]],
        ['nombre' => 'Ingeniería de Software', 'carreras' => ['Informática' => 6]],

        // ── Especialidades nuevas (Contaduría) ──────────────────────────────
        ['nombre' => 'Impuestos II', 'carreras' => ['Contaduría' => 6]],
        ['nombre' => 'Auditoría Financiera', 'carreras' => ['Contaduría' => 6]],

        // ── Especialidades nuevas (Diseño Gráfico) ──────────────────────────
        ['nombre' => 'Diseño de Identidad Visual', 'carreras' => ['Diseño Gráfico' => 3]],
        ['nombre' => 'Branding', 'carreras' => ['Diseño Gráfico' => 5]],
        ['nombre' => 'Diseño UX/UI', 'carreras' => ['Diseño Gráfico' => 5]],

        // ── Especialidades nuevas (Marketing Digital) ───────────────────────
        ['nombre' => 'Paid Media', 'carreras' => ['Marketing Digital' => 5]],

        // ── Especialidades nuevas (Turismo) ─────────────────────────────────
        ['nombre' => 'Geografía Turística', 'carreras' => ['Turismo' => 2]],
        ['nombre' => 'Planificación Turística', 'carreras' => ['Turismo' => 5]],
        ['nombre' => 'Eventos y Convenciones', 'carreras' => ['Turismo' => 5]],

        // ── Especialidades nuevas (Enfermería) ──────────────────────────────
        ['nombre' => 'Nutrición y Dietética', 'carreras' => ['Enfermería' => 4]],
        ['nombre' => 'Salud Pública', 'carreras' => ['Enfermería' => 5]],
        ['nombre' => 'Bioestadística', 'carreras' => ['Enfermería' => 5]],
        ['nombre' => 'Administración de Servicios de Salud', 'carreras' => ['Enfermería' => 6]],
        ['nombre' => 'Materno Infantil', 'carreras' => ['Enfermería' => 6]],
        ['nombre' => 'Cuidados del Adulto Mayor', 'carreras' => ['Enfermería' => 6]],

        // ── Especialidades nuevas (Electrónica) ─────────────────────────────
        ['nombre' => 'Sistemas de Control', 'carreras' => ['Electrónica' => 3]],
        ['nombre' => 'Instrumentación y Medidas', 'carreras' => ['Electrónica' => 4]],
        ['nombre' => 'Mantenimiento Electrónico', 'carreras' => ['Electrónica' => 5]],
        ['nombre' => 'Robótica', 'carreras' => ['Electrónica' => 6]],
        ['nombre' => 'Programación de Microcontroladores', 'carreras' => ['Electrónica' => 6]],
        ['nombre' => 'Automatización Industrial', 'carreras' => ['Electrónica' => 6]],
    ];

    public function run(): void
    {
        if (count(self::CATALOGO) !== self::TOTAL_CURSOS) {
            throw new \RuntimeException(
                'El catálogo de cursos debe tener exactamente '.self::TOTAL_CURSOS.' cursos.'
            );
        }

        foreach (array_values(self::CATALOGO) as $i => $item) {
            Curso::create([
                'nombre' => $item['nombre'],
                'limite_estudiantes' => 18 + ($i % 11),
            ]);
        }
    }

    /** Nombres de las materias base (las que comparten varias carreras). */
    public static function materiasBase(): array
    {
        return array_values(array_map(
            fn ($item) => $item['nombre'],
            array_filter(self::CATALOGO, fn ($item) => ! empty($item['base']))
        ));
    }
}