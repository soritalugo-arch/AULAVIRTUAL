# AulaVirtual

Plataforma académica para la gestión de una institución universitaria: autenticación por roles, inscripción de cursos por cuatrimestre, calificaciones, asistencia y reportes.

## Requisitos

| Herramienta  | Versión mínima |
|--------------|----------------|
| PHP          | ^8.3           |
| Composer     | 2.x            |
| Node.js + npm| 20.x / 10.x    |
| PostgreSQL   | 14+            |

El proyecto usa el stack oficial de Laravel 13: Blade + Tailwind CSS (Vite) y Eloquent sobre PostgreSQL.

## Instalación

1. Clonar el repositorio y entrar a la carpeta:

   ```sh
   git clone <url-del-repo> aulavirtual
   cd aulavirtual
   ```

2. Instalar dependencias de PHP y JavaScript:

   ```sh
   composer install
   npm install
   ```

3. Configurar el archivo de entorno:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   Ajustar en `.env` los siguientes valores:

   ```env
   APP_NAME=AulaVirtual
   APP_URL=http://localhost:8000

   APP_LOCALE=es
   APP_FALLBACK_LOCALE=es
   APP_FAKER_LOCALE=es_VE

   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=aulavirtual
   DB_USERNAME=postgres
   DB_PASSWORD=<tu-contraseña>

   SESSION_DRIVER=database
   MAIL_MAILER=log
   ```

   Notas:
   - Usar **un solo nombre de base de datos en minúsculas** (p. ej. `aulavirtual`) para que el archivo sea portable en todo el equipo.
   - `APP_FAKER_LOCALE=es_VE` genera nombres, cédulas y teléfonos venezolanos en el seeder (los teléfonos usan las extensiones `0412`, `0414`, `0416`, `0422`, `0424` y `0426`).
   - `SESSION_DRIVER=database` y `MAIL_MAILER=log` ya vienen por defecto y no deben cambiarse.

4. Crear la base de datos en PostgreSQL:

   ```sh
   psql -U postgres -c "CREATE DATABASE aulavirtual;"
   ```

5. Ejecutar migraciones y seeders:

   ```sh
   php artisan migrate:fresh --seed
   ```

6. Compilar los assets frontend:

   ```sh
   npm run build
   # o, durante desarrollo:
   npm run dev
   ```

7. Levantar el servidor:

   ```sh
   php artisan serve
   ```

   El proyecto queda disponible en <http://localhost:8000>.

## Datos de prueba

`php artisan migrate:fresh --seed` puebla la base con:

| Recurso              | Cantidad |
|----------------------|----------|
| Roles                | 3 (admin, profesor, estudiante) |
| Cuatrimestres        | 3 (Q1 cerrado, Q2 actual, Q3 próximo) |
| Carreras             | 8          |
| Cursos               | 45         |
| Horarios             | 45 (8 slots por día) |
| Profesores           | 25         |
| Estudiantes          | 600        |
| Usuarios             | 626        |
| Inscripciones        | 955        |
| Calificaciones       | 1080       |
| Listas de espera     | 30         |
| Registros de asistencia | 12960   |

Aproximadamente el 10% de los estudiantes (61) tienen deuda pendiente (`deuda = true`).

### Cuentas de inicio de sesión

| Rol      | Email                                    | Contraseña   |
|----------|------------------------------------------|--------------|
| Admin    | maria.rodriguez-rectora@aula.edu         | Rectora2026  |
| Profesor | jose.salazar-profesor@aula.edu           | Profesor2026 |
| Estudiante | alejandro.gonzalez-estudiante@aula.edu | Alejandro2026 |

El resto de usuarios generados aleatoriamente usan la contraseña `password`.

### Casos de prueba embebidos

Los seeders ya dejan datos listos para validar las reglas de negocio:

| Email                              | Caso                                                        |
|------------------------------------|-------------------------------------------------------------|
| luis.marcano-conflicto@aula.edu    | Inscrito en 2 cursos con la misma hora (slot 0)             |
| jose.salazar-profesor@aula.edu     | El profesor que dicta esos 2 cursos solapados               |
| gabriela.castillo-deuda@aula.edu   | Estudiante con deuda sin inscribir este cuatrimestre        |
| valentina.rojas-egresada@aula.edu  | Aprobó los 6 cursos de Diseño Gráfico en Q1 (certificable)  |
| diego.tovar-repitiente@aula.edu    | Nota 4 en Base de Datos I en Q1, la repite en Q2            |
| carlos.fuentes-inasistente@aula.edu | 5 de 12 fallas (41.7 %) en un curso                        |
| andreina.quintero-alerta@aula.edu  | 3 de 12 fallas (25 %) en un curso                           |
| sofia.mendez-puntual@aula.edu      | Asistencia perfecta                                         |

Además, 8 cursos están con cupo lleno (Fundamentos de Programación, Contabilidad I, Diseño Editorial, Marketing Digital I, Ecoturismo, Enfermería Básica, Electrónica Básica y Matemática Básica) y tienen listas de espera generadas.

## Paneles por rol

| Rol      | Ruta        | Acceso                    |
|----------|-------------|---------------------------|
| Admin    | `/admin`    | Gestión académica, reportes |
| Profesor | `/profesor` | Cursos, calificaciones, asistencia |
| Estudiante | `/estudiante` | Inscripción, horario, notas |

Las rutas exigen autenticación y el rol correcto a través del middleware `role` (`EnsureRole`). Si un estudiante accede a `/admin` recibe un `403 Forbidden`.

## Tests, lint y utilidades

```sh
composer test          # Ejecuta la suite de PHPUnit
./vendor/bin/pint      # Formatea el código con Laravel Pint
php artisan route:list # Lista las rutas registradas
php artisan db:seed    # Re-ejecuta solo los seeders
```

## Convenciones del equipo

- **Rutas**: un archivo por módulo (`routes/inscripcion.php`, `routes/calificacion.php`, etc.) incluido desde `routes/web.php`.
- **Seeders**: `DatabaseSeeder` está congelado. Cambios de datos en el futuro se hacen con migraciones, no editando seeders.
- **Base**: el layout base está en `resources/views/layouts/app.blade.php`, con menú por rol y el hook `@yield('menu_extra')` para los módulos.