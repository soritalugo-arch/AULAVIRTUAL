<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const EMAIL_ADMIN = 'maria.rodriguez-rectora@aula.edu';
    public const EMAIL_PROFESOR = 'jose.salazar-profesor@aula.edu';
    public const EMAIL_ESTUDIANTE = 'alejandro.gonzalez-estudiante@aula.edu';
    public const EMAIL_DEUDA = 'gabriela.castillo-deuda@aula.edu';
    public const EMAIL_EGRESADA = 'valentina.rojas-egresada@aula.edu';
    public const EMAIL_CONFLICTO = 'luis.marcano-conflicto@aula.edu';
    public const EMAIL_INASISTENTE = 'carlos.fuentes-inasistente@aula.edu';
    public const EMAIL_ALERTA = 'andreina.quintero-alerta@aula.edu';
    public const EMAIL_REPITIENTE = 'diego.tovar-repitiente@aula.edu';
    public const EMAIL_PUNTUAL = 'sofia.mendez-puntual@aula.edu';

    public const CONTRASENA_DE_EMAIL = [
        self::EMAIL_ADMIN => 'Rectora2026',
        self::EMAIL_PROFESOR => 'Profesor2026',
        self::EMAIL_ESTUDIANTE => 'Alejandro2026',
        self::EMAIL_DEUDA => 'Gabriela2026',
        self::EMAIL_EGRESADA => 'Valentina2026',
        self::EMAIL_CONFLICTO => 'Luis2026',
        self::EMAIL_INASISTENTE => 'Carlos2026',
        self::EMAIL_ALERTA => 'Andreina2026',
        self::EMAIL_REPITIENTE => 'Diego2026',
        self::EMAIL_PUNTUAL => 'Sofia2026',
    ];

    public const CURSOS_CONFLICTO = [
        'Fundamentos de Programación',
        'Inglés Técnico',
    ];

    public const CURSOS_LLENOS = [
        'Fundamentos de Programación',
        'Contabilidad I',
        'Diseño Editorial',
        'Marketing Digital I',
        'Ecoturismo',
        'Enfermería Básica',
        'Electrónica Básica',
        'Matemática Básica',
    ];

    public const CARRERA_EGRESADA = 'Diseño Gráfico';

    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            CuatrimestreSeeder::class,
            CarreraSeeder::class,
            CursoSeeder::class,
            CursoCarreraSeeder::class,
            CursoCuatrimestreSeeder::class,
            UsuarioSeeder::class,
            ProfesorSeeder::class,
            EstudianteSeeder::class,
            CursoProfesorSeeder::class,
            HorarioSeeder::class,
            InscripcionSeeder::class,
            ListaEsperaSeeder::class,
            CalificacionSeeder::class,
            AsistenciaSeeder::class,
        ]);
    }
}