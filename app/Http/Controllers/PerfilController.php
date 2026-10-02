<?php

namespace App\Http\Controllers;

use App\Services\HistorialService;
use Illuminate\Http\Request;

/**
 * "Mis datos": la ficha del usuario conectado, con lo propio de su rol.
 *
 * Una sola pagina para los tres roles: lo comun es lo del usuario (nombre,
 * correo), y a cada rol se le anaden sus datos particulares (la carrera y el
 * estado academico al estudiante; los cursos que dicta al profesor).
 */
class PerfilController extends Controller
{
    public function show(Request $request, HistorialService $historial)
    {
        $usuario = $request->user();
        $usuario->load('roles');

        $rol = $usuario->roles->pluck('nombre')->first() ?? 'sin rol';

        $datos = [
            'carrera' => null,
            'cedula' => null,
            'deuda' => false,
            'egresado' => false,
            'cursos' => collect(),
        ];

        if ($rol === 'estudiante' && $usuario->estudiante) {
            $datos['carrera'] = $usuario->estudiante->carrera?->nombre;
            $datos['cedula'] = $usuario->estudiante->cedula;
            $datos['deuda'] = $usuario->estudiante->deuda;
            $datos['egresado'] = $historial->esEgresado($usuario->estudiante);
        }

        if ($rol === 'profesor' && $usuario->profesor) {
            $datos['cursos'] = $usuario->profesor->cursos()->orderBy('nombre')->get();
        }

        return view('perfil', [
            'usuario' => $usuario,
            'rol' => $rol,
            'datos' => $datos,
        ]);
    }
}