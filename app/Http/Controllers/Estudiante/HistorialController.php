<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Cuatrimestre;
use App\Models\Estudiante;
use App\Services\HistorialService;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Historial academico y certificado de notas del estudiante.
 *
 * Ninguno de los dos metodos recibe un id de estudiante: el registro sale
 * siempre del usuario autenticado. Esa es la garantia de que un alumno solo ve
 * lo suyo, no una comparacion de authorization en cada vista. La ruta ya
 * exige el rol estudiante, asi que un administrador caeria en 403 antes de
 * llegar aqui.
 */
class HistorialController extends Controller
{
    public function __construct(private HistorialService $historial) {}

    public function index(Request $request)
    {
        $estudiante = $this->estudiante();
        $datos = $this->historial->historial($estudiante);

        return view('estudiante.historial', [
            'estudiante' => $estudiante,
            'datos' => $datos,
            'esEgresado' => $this->historial->esEgresado($estudiante, $datos),
        ]);
    }

    /**
     * Boleta del lapso académico (período), vista imprimible en pantalla.
     *
     * Se emite solo para períodos cerrados: ahí las notas y la asistencia ya
     * son definitivas. La boleta es por lapso académico (el cuatrimestre del
     * período), no por cuatrimestre del plan; el certificado PDF enriquecido
     * sigue siendo exclusivo de los egresados.
     */
    public function boleta(Cuatrimestre $cuatrimestre)
    {
        $estudiante = $this->estudiante();
        $datos = $this->historial->historial($estudiante);

        $periodo = collect($datos['periodos'])->first(
            fn (array $p) => $p['cuatrimestre']->id_cuatrimestre === $cuatrimestre->id_cuatrimestre
        );

        if (! $periodo || $cuatrimestre->estado !== Cuatrimestre::ESTADO_FINALIZADO) {
            abort(404, 'No hay una boleta emitida para ese lapso.');
        }

        return view('estudiante.boleta', [
            'estudiante' => $estudiante,
            'cuatrimestre' => $cuatrimestre,
            'datos' => $datos,
            'periodo' => $periodo,
        ]);
    }

    /**
     * Certificado de notas en PDF, listo para imprimir.
     *
     * Requisito legal: se emite cuando el estudiante TERMINA su carrera, no
     * antes. El boton de la vista ya se deshabilita para quien no egreso, pero
     * la ruta tambien lo exige, por si alguien escribe la URL a mano.
     */
    public function certificado(): Response
    {
        $estudiante = $this->estudiante();
        $datos = $this->historial->historial($estudiante);

        if (! $this->historial->esEgresado($estudiante, $datos)) {
            abort(403, 'El certificado se emite cuando el estudiante termina su carrera.');
        }

        $pdf = $this->pdf()
            ->loadView('reportes.certificado', [
                'estudiante' => $estudiante,
                'datos' => $datos,
                'emitido' => now(),
            ])
            ->setPaper('a4', 'portrait')
            // DejaVu Sans viene incluida en dompdf y trae las tildes y la enye.
            // Las fuentes estandar del PDF tambien las tienen, pero no todas las
            // versiones de la plataforma compilan los glyphs por igual.
            ->setOption('defaultFont', 'DejaVu Sans')
            // La plantilla es autonoma: nada de CSS ni imagenes remotas, para que
            // el certificado se genere igual sin conexion.
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true);

        $pdf->getDomPDF()->add_info('Title', 'Certificado de notas');

        // stream y no download: el certificado se abre en una pestana del
        // navegador, de donde el estudiante decide guardarlo o imprimirlo.
        return $pdf->stream($this->nombreArchivo($estudiante));
    }

    private function estudiante(): Estudiante
    {
        return request()->user()->estudiante;
    }

    /** El paquete no publica un facade en esta version, se resuelve del contenedor. */
    private function pdf(): PDF
    {
        return app('dompdf.wrapper');
    }

    /**
     * Nombre sin tildes ni espacios: va en la cabecera Content-Disposition y
     * un nombre con acentos se ve roto en algunos navegadores.
     */
    private function nombreArchivo(Estudiante $estudiante): string
    {
        $identificador = $estudiante->cedula ?: $estudiante->id_usuario;

        return 'certificado-notas-'.$identificador.'.pdf';
    }
}
