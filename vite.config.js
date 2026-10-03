import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/auth/login.css',
                'resources/js/auth/login.js',
                'resources/js/admin/asignacion-prof.js',
                'resources/js/admin/deudas.js',
                'resources/js/admin/graficos.js',
                'resources/js/admin/periodo.js',
                'resources/js/admin/rendimiento.js',
                'resources/css/estudiante/boleta.css',
                'resources/js/estudiante/boleta.js',
                'resources/css/estudiante/historial.css',
                'resources/css/estudiante/matriculacion.css',
                'resources/js/estudiante/matriculacion.js',
                'resources/css/estudiante/notas.css',
                'resources/css/layouts/app.css',
                'resources/js/layouts/app.js',
                'resources/css/profesor/asistencia.css',
                'resources/js/profesor/asistencia.js',
                'resources/css/profesor/mis-cursos.css',
                'resources/css/profesor/notas.css',
                'resources/js/profesor/notas.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
