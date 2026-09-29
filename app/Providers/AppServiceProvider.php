<?php

namespace App\Providers;

use Dompdf\Dompdf;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email'));
        });

        // dompdf recorta la fuente del PDF a los glifos que el documento usa, y
        // para escribir esa fuente recortada pide un temporal (Cpdf.php:1266). Con
        // el temporal del sistema, tempnam() a veces no encuentra donde crear el
        // archivo y la descarga revienta con "Path must not be empty", dejando al
        // estudiante sin certificado. Se le pasa uno dentro de storage, que es
        // donde la aplicacion ya escribe de todas formas.
        //
        // El ajuste va en las opciones y no sobre el objeto porque Cpdf copia la
        // ruta del temporal en su constructor, que corre dentro de "new Dompdf":
        // cambiarla despues llega tarde y el recorte sigue usando %TEMP%.
        $this->app->extend('dompdf.options', function (array $opciones): array {
            $temporal = storage_path('framework/dompdf');

            File::ensureDirectoryExists($temporal, 0755, true);

            return array_merge($opciones, ['temp_dir' => $temporal]);
        });

        // Y si tampoco en storage se puede escribir, se renuncia al recorte: pesa
        // mas, pero el certificado sale igual en vez de un error 500.
        $this->app->afterResolving('dompdf', function (Dompdf $dompdf): void {
            $sonda = @tempnam($dompdf->getOptions()->getTempDir(), 'dompdf_prueba_');

            $dompdf->getOptions()->setIsFontSubsettingEnabled($sonda !== false);

            if ($sonda !== false) {
                @unlink($sonda);
            }
        });
    }
}
