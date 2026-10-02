<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;

/**
 * Catalogo completo de materias del instituto: 45 en total.
 *
 * Cada materia dice a que carreras pertenece y en que etapa del plan de esa
 * carrera se dicta (1 = primer cuatrimestre del recorrido, 2 = el segundo...).
 * Las materias base (Matematica, Ingles, Ofimatica, Expresion Oral y
 * Emprendimiento) se comparten entre varias carreras; el resto son
 * especialidades que algunas carreras tambien comparten cuando tienen sentido.
 *
 * Asi cada carrera recorre 10 a 12 materias en 5 cuatrimestres (2 a 3 materias
 * por cuatrimestre), sin salir del catalogo de 45.
 */
class CursoSeeder extends Seeder
{
    /** Total de cursos del catalogo; se verifica en run() para no romper los demás seeders. */
    public const TOTAL_CURSOS = 45;

    public const CATALOGO = [
        // ── Materias base (compartidas entre carreras) ────────────────────
        ['nombre' => 'Matemática Básica', 'base' => true, 'carreras' => [
            'Informática' => 1, 'Contaduría' => 1, 'Administración' => 1, 'Electrónica' => 1, 'Enfermería' => 5,
        ]],
        ['nombre' => 'Inglés Técnico', 'base' => true, 'carreras' => [
            'Informática' => 2, 'Contaduría' => 1, 'Administración' => 1, 'Marketing Digital' => 1, 'Turismo' => 1, 'Enfermería' => 2, 'Electrónica' => 5,
        ]],
        ['nombre' => 'Ofimática', 'base' => true, 'carreras' => [
            'Informática' => 1, 'Contaduría' => 1, 'Administración' => 1, 'Marketing Digital' => 1, 'Turismo' => 1, 'Enfermería' => 1, 'Electrónica' => 5,
        ]],
        ['nombre' => 'Expresión Oral y Escrita', 'base' => true, 'carreras' => [
            'Administración' => 2, 'Marketing Digital' => 1, 'Turismo' => 2, 'Diseño Gráfico' => 1, 'Enfermería' => 5,
        ]],
        ['nombre' => 'Emprendimiento', 'base' => true, 'carreras' => [
            'Informática' => 5, 'Administración' => 3, 'Contaduría' => 2, 'Diseño Gráfico' => 2, 'Marketing Digital' => 2, 'Turismo' => 5, 'Electrónica' => 4,
        ]],

        // ── Informática ─────────────────────────────────────────────────────
        ['nombre' => 'Fundamentos de Programación', 'carreras' => ['Informática' => 2, 'Electrónica' => 2]],
        ['nombre' => 'Base de Datos I', 'carreras' => ['Informática' => 3]],
        ['nombre' => 'Programación II', 'carreras' => ['Informática' => 3]],
        ['nombre' => 'Redes de Computadoras', 'carreras' => ['Informática' => 4, 'Electrónica' => 3]],
        ['nombre' => 'Desarrollo Web', 'carreras' => ['Informática' => 5]],
        ['nombre' => 'Sistemas Operativos', 'carreras' => ['Informática' => 4, 'Electrónica' => 4]],

        // ── Administración ──────────────────────────────────────────────────
        ['nombre' => 'Administración General', 'carreras' => ['Administración' => 2, 'Contaduría' => 3]],
        ['nombre' => 'Gestión de Recursos Humanos', 'carreras' => ['Administración' => 4]],
        ['nombre' => 'Finanzas Empresariales', 'carreras' => ['Administración' => 5]],
        ['nombre' => 'Derecho Laboral', 'carreras' => ['Administración' => 5]],
        ['nombre' => 'Mercadotecnia', 'carreras' => ['Administración' => 4, 'Marketing Digital' => 5, 'Turismo' => 5]],

        // ── Contaduría ──────────────────────────────────────────────────────
        ['nombre' => 'Contabilidad I', 'carreras' => ['Administración' => 3, 'Contaduría' => 2]],
        ['nombre' => 'Contabilidad II', 'carreras' => ['Contaduría' => 3]],
        ['nombre' => 'Contabilidad de Costos', 'carreras' => ['Contaduría' => 4]],
        ['nombre' => 'Impuestos I', 'carreras' => ['Contaduría' => 5]],
        ['nombre' => 'Auditoría Básica', 'carreras' => ['Contaduría' => 5]],
        ['nombre' => 'Matemática Financiera', 'carreras' => ['Contaduría' => 4]],

        // ── Diseño Gráfico ──────────────────────────────────────────────────
        ['nombre' => 'Fundamentos del Diseño', 'carreras' => ['Diseño Gráfico' => 1, 'Marketing Digital' => 3]],
        ['nombre' => 'Ilustración Digital', 'carreras' => ['Diseño Gráfico' => 3]],
        ['nombre' => 'Diseño Editorial', 'carreras' => ['Diseño Gráfico' => 3]],
        ['nombre' => 'Diseño Publicitario', 'carreras' => ['Diseño Gráfico' => 4, 'Marketing Digital' => 4]],
        ['nombre' => 'Fundamentos de Color y Composición', 'carreras' => ['Diseño Gráfico' => 2]],

        // ── Marketing Digital ───────────────────────────────────────────────
        ['nombre' => 'Marketing Digital I', 'carreras' => ['Marketing Digital' => 2, 'Diseño Gráfico' => 5]],
        ['nombre' => 'Community Management', 'carreras' => ['Marketing Digital' => 3, 'Diseño Gráfico' => 4]],
        ['nombre' => 'SEO y SEM', 'carreras' => ['Marketing Digital' => 4]],
        ['nombre' => 'Analítica Web', 'carreras' => ['Marketing Digital' => 5]],
        ['nombre' => 'Estrategias de Contenido', 'carreras' => ['Marketing Digital' => 5, 'Diseño Gráfico' => 5]],

        // ── Turismo ─────────────────────────────────────────────────────────
        ['nombre' => 'Fundamentos del Turismo', 'carreras' => ['Turismo' => 2]],
        ['nombre' => 'Gestión Hotelera', 'carreras' => ['Turismo' => 3]],
        ['nombre' => 'Ecoturismo', 'carreras' => ['Turismo' => 4]],
        ['nombre' => 'Atención al Cliente y Protocolo', 'carreras' => ['Turismo' => 3, 'Administración' => 5, 'Enfermería' => 4]],
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
        ['nombre' => 'Instalaciones Eléctricas', 'carreras' => ['Electrónica' => 3]],
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