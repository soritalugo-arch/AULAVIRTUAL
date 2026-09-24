<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    public const CATALOGO = [
        ['nombre' => 'Matemática Básica', 'carreras' => ['Informática', 'Electrónica', 'Contaduría']],
        ['nombre' => 'Inglés Técnico', 'carreras' => ['Turismo', 'Administración']],
        ['nombre' => 'Ofimática', 'carreras' => ['Administración', 'Contaduría']],
        ['nombre' => 'Expresión Oral y Escrita', 'carreras' => ['Marketing Digital', 'Turismo', 'Administración']],
        ['nombre' => 'Emprendimiento', 'carreras' => ['Diseño Gráfico', 'Marketing Digital', 'Administración']],
        ['nombre' => 'Fundamentos de Programación', 'carreras' => ['Informática']],
        ['nombre' => 'Base de Datos I', 'carreras' => ['Informática']],
        ['nombre' => 'Programación II', 'carreras' => ['Informática']],
        ['nombre' => 'Redes de Computadoras', 'carreras' => ['Informática']],
        ['nombre' => 'Desarrollo Web', 'carreras' => ['Informática']],
        ['nombre' => 'Sistemas Operativos', 'carreras' => ['Informática']],
        ['nombre' => 'Administración General', 'carreras' => ['Administración']],
        ['nombre' => 'Gestión de Recursos Humanos', 'carreras' => ['Administración']],
        ['nombre' => 'Finanzas Empresariales', 'carreras' => ['Administración']],
        ['nombre' => 'Derecho Laboral', 'carreras' => ['Administración']],
        ['nombre' => 'Mercadotecnia', 'carreras' => ['Administración']],
        ['nombre' => 'Contabilidad I', 'carreras' => ['Contaduría']],
        ['nombre' => 'Contabilidad II', 'carreras' => ['Contaduría']],
        ['nombre' => 'Contabilidad de Costos', 'carreras' => ['Contaduría']],
        ['nombre' => 'Impuestos I', 'carreras' => ['Contaduría']],
        ['nombre' => 'Auditoría Básica', 'carreras' => ['Contaduría']],
        ['nombre' => 'Matemática Financiera', 'carreras' => ['Contaduría']],
        ['nombre' => 'Fundamentos del Diseño', 'carreras' => ['Diseño Gráfico']],
        ['nombre' => 'Ilustración Digital', 'carreras' => ['Diseño Gráfico']],
        ['nombre' => 'Diseño Editorial', 'carreras' => ['Diseño Gráfico']],
        ['nombre' => 'Diseño Publicitario', 'carreras' => ['Diseño Gráfico']],
        ['nombre' => 'Fundamentos de Color y Composición', 'carreras' => ['Diseño Gráfico']],
        ['nombre' => 'Marketing Digital I', 'carreras' => ['Marketing Digital']],
        ['nombre' => 'Community Management', 'carreras' => ['Marketing Digital']],
        ['nombre' => 'SEO y SEM', 'carreras' => ['Marketing Digital']],
        ['nombre' => 'Analítica Web', 'carreras' => ['Marketing Digital']],
        ['nombre' => 'Estrategias de Contenido', 'carreras' => ['Marketing Digital']],
        ['nombre' => 'Fundamentos del Turismo', 'carreras' => ['Turismo']],
        ['nombre' => 'Gestión Hotelera', 'carreras' => ['Turismo']],
        ['nombre' => 'Ecoturismo', 'carreras' => ['Turismo']],
        ['nombre' => 'Atención al Cliente y Protocolo', 'carreras' => ['Turismo']],
        ['nombre' => 'Turismo Sostenible', 'carreras' => ['Turismo']],
        ['nombre' => 'Anatomía y Fisiología', 'carreras' => ['Enfermería']],
        ['nombre' => 'Enfermería Básica', 'carreras' => ['Enfermería']],
        ['nombre' => 'Farmacología General', 'carreras' => ['Enfermería']],
        ['nombre' => 'Primeros Auxilios', 'carreras' => ['Enfermería']],
        ['nombre' => 'Ética Profesional en Salud', 'carreras' => ['Enfermería']],
        ['nombre' => 'Electrónica Básica', 'carreras' => ['Electrónica']],
        ['nombre' => 'Circuitos Digitales', 'carreras' => ['Electrónica']],
        ['nombre' => 'Instalaciones Eléctricas', 'carreras' => ['Electrónica']],
    ];

    public function run(): void
    {
        if (count(self::CATALOGO) !== 45) {
            throw new \RuntimeException('El catálogo de cursos debe tener exactamente 45 cursos.');
        }

        foreach (array_values(self::CATALOGO) as $i => $item) {
            Curso::create([
                'nombre' => $item['nombre'],
                'limite_estudiantes' => 18 + ($i % 11),
            ]);
        }
    }
}