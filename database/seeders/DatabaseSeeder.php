<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const EMAIL_ADMIN = 'rectora@aula.edu';
    public const EMAIL_PROFESOR = 'profesor@aula.edu';
    public const EMAIL_ESTUDIANTE = 'estudiante@aula.edu';
    public const EMAIL_DEUDA = 'deuda@aula.edu';
    public const EMAIL_EGRESADA = 'egresada@aula.edu';
    public const EMAIL_CONFLICTO = 'conflicto@aula.edu';
    public const EMAIL_INASISTENTE = 'inasistente@aula.edu';
    public const EMAIL_ALERTA = 'alerta@aula.edu';
    public const EMAIL_REPITIENTE = 'repitiente@aula.edu';
    public const EMAIL_PUNTUAL = 'puntual@aula.edu';

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