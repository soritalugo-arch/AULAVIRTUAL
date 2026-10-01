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

📧 Guía para la Simulación de Envío de Correo Electrónico y Colas (Mailtrap)

Esta guía está diseñada para que todos los miembros del equipo puedan configurar, probar de manera segura el envío de correos electrónicos automáticos (como las notificaciones de notas y avisos de cursos) y gestionar los procesos en segundo plano (queues) en su entorno de desarrollo local.

1. ¿Cómo buscar y acceder a Mailtrap?

Abre tu navegador web de preferencia.

Ingresa a la página oficial: https://mailtrap.io/

Haz clic en el botón de registro (Sign Up o Get Started for Free). Puedes crear una cuenta gratuita vinculando tu cuenta de GitHub, Google o mediante correo electrónico.

2. Obtener las credenciales de prueba

Una vez dentro de tu cuenta en Mailtrap:

En el panel principal, dirígete a Email Testing y selecciona Inboxes.

Haz clic sobre tu bandeja de entrada por defecto (o crea una nueva con el botón Add Inbox).

Dentro de tu bandeja, ve a la pestaña o sección de Integrations (Integrations).

Selecciona Laravel en el menú desplegable de frameworks para ver las credenciales exactas.

3. Configuración del archivo .env

Abre el archivo .env en la raíz de tu proyecto local de Laravel y actualiza o agrega las credenciales de correo proporcionadas por Mailtrap, configurando también la conexión de colas:

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_proporcionado_por_mailtrap
MAIL_PASSWORD=tu_contraseña_proporcionada_por_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="soporte@aulavirtual.com"
MAIL_FROM_NAME="Aula Virtual"

# Configuración de Colas
QUEUE_CONNECTION=database


4. Preparación de la Base de Datos para Colas

Si el sistema gestiona los correos mediante tareas en cola (Queued Jobs), asegúrate de tener la tabla de colas creada en tu base de datos local ejecutando:

php artisan queue:table
php artisan migrate


5. Ejecutar el Procesador de Envíos en Segundo Plano

Para que Laravel procese y envíe automáticamente los correos que se van generando (por ejemplo, al guardar una nota), debes mantener una terminal abierta ejecutando el comando de escucha:

php artisan queue:listen


(Nota: Alternativamente, puedes usar php artisan queue:work si prefieres que procese los trabajos de manera continua y más ligera sin recargar todo el framework).

6. ¿Cómo realizar la simulación y prueba completa?

Para verificar el funcionamiento de punta a punta:

Terminal 1: Inicia el servidor web local con:

php artisan serve


Terminal 2: Mantén activo el oyente de colas con:

php artisan queue:listen


Ingresa a la aplicación web local con un usuario con permisos de Profesor.

Dirígete al módulo de calificaciones, modifica o guarda la nota final de un estudiante. Al hacerlo, el sistema encolará el envío y la Terminal 2 procesará la tarea automáticamente.

Regresa a tu panel de Mailtrap en el navegador y revisa tu bandeja de entrada (Inbox). Verás el correo electrónico con su diseño completo listo para ser validado.

## Convenciones del equipo

- **Rutas**: un archivo por módulo (`routes/inscripcion.php`, `routes/calificacion.php`, etc.) incluido desde `routes/web.php`.
- **Seeders**: `DatabaseSeeder` está congelado. Cambios de datos en el futuro se hacen con migraciones, no editando seeders.
- **Base**: el layout base está en `resources/views/layouts/app.blade.php`, con menú por rol y el hook `@yield('menu_extra')` para los módulos.
